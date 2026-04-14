<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\User;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommandeController extends AbstractController
{
    #[Route('/commande/recap', name: 'app_commande_recap')]
    #[IsGranted('ROLE_USER')]
    public function recap(SessionInterface $session, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $panier = $session->get('panier', []);

        if ([] === $panier) {
            $this->addFlash('warning', 'Votre panier est vide.');

            return $this->redirectToRoute('app_panier');
        }

        $panierData = [];
        $total = 0.0;

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $prix = $produit->getPrixFloat();
                $sousTotal = $prix * (int) $quantite;
                $panierData[] = [
                    'produit' => $produit,
                    'quantite' => (int) $quantite,
                    'sousTotal' => $sousTotal,
                ];
                $total += $sousTotal;
            }
        }

        $fraisPort = $total >= 100.0 ? 0.0 : 5.99;

        return $this->render('commande/recap.html.twig', [
            'panierData' => $panierData,
            'total' => $total,
            'fraisPort' => $fraisPort,
            'totalAvecPort' => $total + $fraisPort,
            'categories' => $categorieRepository->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/commande/confirmer', name: 'app_commande_confirmer', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function confirmer(Request $request, SessionInterface $session, ProduitRepository $produitRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        if (!$this->isCsrfTokenValid('commande_confirmer', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'Session expirée ou jeton invalide. Réessayez depuis le récapitulatif.');

            return $this->redirectToRoute('app_commande_recap');
        }

        $panier = $session->get('panier', []);

        if ([] === $panier) {
            return $this->redirectToRoute('app_panier');
        }

        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $total = 0.0;
        $lignes = [];

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $prix = $produit->getPrixFloat();
                $q = (int) $quantite;
                $total += $prix * $q;
                $lignes[] = [
                    'produit' => $produit,
                    'quantite' => $q,
                    'prix' => number_format($prix, 2, '.', ''),
                ];
            }
        }

        $fraisPort = $total >= 100.0 ? 0.0 : 5.99;
        $montantTotal = $total + $fraisPort;

        $commande = new Commande();
        $commande->setUser($user);
        $commande->setDateCommande(new \DateTime());
        $commande->setStatut('en_attente');
        $commande->setFraisPort(number_format($fraisPort, 2, '.', ''));
        $commande->setMontantTotal(number_format($montantTotal, 2, '.', ''));
        $em->persist($commande);

        foreach ($lignes as $ligne) {
            $ligneCommande = new LigneCommande();
            $ligneCommande->setCommande($commande);
            $ligneCommande->setProduit($ligne['produit']);
            $ligneCommande->setQuantite($ligne['quantite']);
            $ligneCommande->setPrixUnitaire($ligne['prix']);
            $em->persist($ligneCommande);
        }

        $em->flush();
        $session->remove('panier');
        $this->addFlash('success', 'Commande enregistrée avec succès !');

        if ($this->getParameter('app.send_order_email')) {
            try {
                $message = (new Email())
                    ->from((string) $this->getParameter('app.mailer_from'))
                    ->to($user->getEmail())
                    ->subject(sprintf('Sneakers Shop — commande n°%d', (int) $commande->getId()))
                    ->html($this->renderView('emails/commande_confirmee.html.twig', ['commande' => $commande]));
                $mailer->send($message);
                $this->addFlash('info', 'Un email de confirmation a été envoyé.');
            } catch (\Throwable) {
                $this->addFlash('warning', 'La commande est enregistrée, mais l’email n’a pas pu être envoyé.');
            }
        }

        return $this->redirectToRoute('app_commande_confirmation', ['id' => $commande->getId()]);
    }

    #[Route('/commande/confirmation/{id}', name: 'app_commande_confirmation')]
    #[IsGranted('ROLE_USER')]
    public function confirmation(int $id, CommandeRepository $commandeRepository, CategorieRepository $categorieRepository): Response
    {
        $commande = $commandeRepository->find($id);
        if (!$commande instanceof Commande) {
            throw $this->createNotFoundException();
        }

        $current = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN')
            && (!$current instanceof User || $commande->getUser()?->getId() !== $current->getId())) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/confirmation.html.twig', [
            'commande' => $commande,
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/commande/historique', name: 'app_commande_historique')]
    #[IsGranted('ROLE_USER')]
    public function historique(CommandeRepository $commandeRepository, CategorieRepository $categorieRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/historique.html.twig', [
            'commandes' => $commandeRepository->findBy(['user' => $user], ['dateCommande' => 'DESC']),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    /**
     * Détail d’une commande pour le client connecté (depuis « Mes commandes »).
     * L’URL /commande/{id} n’entre pas en conflit avec /commande/historique car {id} est numérique uniquement.
     */
    #[Route('/commande/{id}', name: 'app_commande_client_show', requirements: ['id' => '\\d+'])]
    #[IsGranted('ROLE_USER')]
    public function clientShow(Commande $commande, CategorieRepository $categorieRepository): Response
    {
        $current = $this->getUser();
        if (!$current instanceof User || $commande->getUser()?->getId() !== $current->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('commande/client_show.html.twig', [
            'commande' => $commande,
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/admin/commandes', name: 'app_commande_index')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(CommandeRepository $commandeRepository, CategorieRepository $categorieRepository): Response
    {
        return $this->render('commande/index.html.twig', [
            'commandes' => $commandeRepository->findBy([], ['dateCommande' => 'DESC']),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/admin/commandes/{id}', name: 'app_commande_show')]
    #[IsGranted('ROLE_ADMIN')]
    public function show(Commande $commande, CategorieRepository $categorieRepository): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/admin/commandes/{id}/statut', name: 'app_commande_statut', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateStatut(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('statut'.$commande->getId(), $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $statut = $request->request->getString('statut');
        if ('' !== $statut) {
            $commande->setStatut($statut);
            $em->flush();
            $this->addFlash('success', 'Statut de la commande mis à jour.');
        }

        return $this->redirectToRoute('app_commande_show', ['id' => $commande->getId()]);
    }

    #[Route('/admin/commandes/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$commande->getId(), $request->request->getString('_token'))) {
            $em->remove($commande);
            $em->flush();
            $this->addFlash('success', 'Commande supprimée.');
        }

        return $this->redirectToRoute('app_commande_index');
    }
}
