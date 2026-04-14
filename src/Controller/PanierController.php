<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class PanierController extends AbstractController
{
    #[Route('/panier', name: 'app_panier')]
    public function index(SessionInterface $session, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $panier = $session->get('panier', []);
        $panierData = [];
        $total = 0.0;

        foreach ($panier as $id => $quantite) {
            $produit = $produitRepository->find($id);
            if ($produit) {
                $prix = $produit->getPrixFloat();
                $q = (int) $quantite;
                $sousTotal = $prix * $q;
                $panierData[] = [
                    'produit' => $produit,
                    'quantite' => $q,
                    'sousTotal' => $sousTotal,
                ];
                $total += $sousTotal;
            }
        }

        $fraisPort = $total >= 100.0 ? 0.0 : 5.99;

        return $this->render('panier/index.html.twig', [
            'panierData' => $panierData,
            'total' => $total,
            'fraisPort' => $fraisPort,
            'totalAvecPort' => $total + $fraisPort,
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/panier/ajouter/{id}', name: 'app_panier_ajouter')]
    public function ajouter(int $id, SessionInterface $session, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        $panier = $session->get('panier', []);
        $panier[$id] = isset($panier[$id]) ? (int) $panier[$id] + 1 : 1;
        $session->set('panier', $panier);

        $this->addFlash('success', '"'.$produit->getNom().'" ajouté au panier !');

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/modifier/{id}', name: 'app_panier_modifier', methods: ['POST'])]
    public function modifier(int $id, Request $request, SessionInterface $session): Response
    {
        if (!$this->isCsrfTokenValid('panier_modifier', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'Jeton de sécurité invalide.');

            return $this->redirectToRoute('app_panier');
        }

        $panier = $session->get('panier', []);
        $quantite = (int) $request->request->get('quantite', 0);

        if ($quantite <= 0) {
            unset($panier[$id]);
        } else {
            $panier[$id] = $quantite;
        }

        $session->set('panier', $panier);

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/supprimer/{id}', name: 'app_panier_supprimer')]
    public function supprimer(int $id, SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);
        unset($panier[$id]);
        $session->set('panier', $panier);
        $this->addFlash('info', 'Produit retiré du panier.');

        return $this->redirectToRoute('app_panier');
    }

    #[Route('/panier/vider', name: 'app_panier_vider')]
    public function vider(SessionInterface $session): Response
    {
        $session->remove('panier');
        $this->addFlash('info', 'Panier vidé.');

        return $this->redirectToRoute('app_panier');
    }
}
