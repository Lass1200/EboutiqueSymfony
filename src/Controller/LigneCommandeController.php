<?php

namespace App\Controller;

use App\Entity\LigneCommande;
use App\Form\LigneCommandeType;
use App\Repository\CategorieRepository;
use App\Repository\LigneCommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/ligne-commande')]
#[IsGranted('ROLE_ADMIN')]
final class LigneCommandeController extends AbstractController
{
    #[Route(name: 'app_ligne_commande_index', methods: ['GET'])]
    public function index(LigneCommandeRepository $ligneCommandeRepository, CategorieRepository $categorieRepository): Response
    {
        return $this->render('ligne_commande/index.html.twig', [
            'ligne_commandes' => $ligneCommandeRepository->findAll(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_ligne_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): Response
    {
        $ligneCommande = new LigneCommande();
        $form = $this->createForm(LigneCommandeType::class, $ligneCommande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ligneCommande);
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ligne_commande/new.html.twig', [
            'ligne_commande' => $ligneCommande,
            'form' => $form->createView(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_commande_show', methods: ['GET'])]
    public function show(LigneCommande $ligneCommande, CategorieRepository $categorieRepository): Response
    {
        return $this->render('ligne_commande/show.html.twig', [
            'ligne_commande' => $ligneCommande,
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_ligne_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, LigneCommande $ligneCommande, EntityManagerInterface $entityManager, CategorieRepository $categorieRepository): Response
    {
        $form = $this->createForm(LigneCommandeType::class, $ligneCommande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ligne_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ligne_commande/edit.html.twig', [
            'ligne_commande' => $ligneCommande,
            'form' => $form->createView(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_ligne_commande_delete', methods: ['POST'])]
    public function delete(Request $request, LigneCommande $ligneCommande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$ligneCommande->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($ligneCommande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ligne_commande_index', [], Response::HTTP_SEE_OTHER);
    }
}
