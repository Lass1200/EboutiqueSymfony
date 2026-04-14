<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(CategorieRepository $categorieRepository, ProduitRepository $produitRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'categories' => $categorieRepository->findAll(),
            'produits' => $produitRepository->findAll(),
        ]);
    }

    #[Route('/categorie/{slug}', name: 'app_categorie_front')]
    public function categorie(string $slug, CategorieRepository $categorieRepository, ProduitRepository $produitRepository): Response
    {
        $categorie = $categorieRepository->findOneBy(['slug' => $slug]);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie introuvable');
        }

        return $this->render('home/categorie.html.twig', [
            'categorie' => $categorie,
            'produits' => $produitRepository->findBy(['categorie' => $categorie]),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/produit/{id}', name: 'app_produit_front')]
    public function produit(int $id, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit introuvable');
        }

        return $this->render('home/produit.html.twig', [
            'produit' => $produit,
            'categories' => $categorieRepository->findAll(),
        ]);
    }
}