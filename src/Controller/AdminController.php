<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(
        CommandeRepository $commandeRepository,
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
        UserRepository $userRepository
    ): Response {
        return $this->render('admin/index.html.twig', [
            'nbCommandes' => count($commandeRepository->findAll()),
            'nbProduits' => count($produitRepository->findAll()),
            'nbCategories' => count($categorieRepository->findAll()),
            'nbUsers' => count($userRepository->findAll()),
            'dernieresCommandes' => $commandeRepository->findBy([], ['dateCommande' => 'DESC'], 5),
            'categories' => $categorieRepository->findAll(),
        ]);
    }
}