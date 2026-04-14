<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Produit;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher) {}

    public function load(ObjectManager $manager): void
    {
        // CATEGORIES
        $categoriesData = [
            ['nom' => 'Running', 'slug' => 'running'],
            ['nom' => 'Basketball', 'slug' => 'basketball'],
            ['nom' => 'Lifestyle', 'slug' => 'lifestyle'],
            ['nom' => 'Skateboard', 'slug' => 'skateboard'],
        ];
        $cats = [];
        foreach ($categoriesData as $d) {
            $cat = new Categorie();
            $cat->setNom($d['nom'])->setSlug($d['slug']);
            $manager->persist($cat);
            $cats[] = $cat;
        }

        // PRODUITS
        $produits = [
            ['nom' => 'Nike Air Max 90', 'prix' => 129.99, 'desc' => 'La légendaire Air Max 90, icône du style depuis 1990. Amorti Air visible pour un confort optimal.', 'cat' => 0],
            ['nom' => 'Nike React Infinity', 'prix' => 159.99, 'desc' => 'Conçue pour réduire les blessures, la React Infinity offre un amorti exceptionnel pour les longues distances.', 'cat' => 0],
            ['nom' => 'Adidas Ultraboost 23', 'prix' => 189.99, 'desc' => 'La chaussure de running ultime avec la technologie Boost pour une énergie maximale à chaque foulée.', 'cat' => 0],
            ['nom' => 'New Balance 1080v12', 'prix' => 174.99, 'desc' => 'Un amorti Fresh Foam X premium pour les runners cherchant le meilleur confort sur route.', 'cat' => 0],
            ['nom' => 'Nike Air Jordan 1', 'prix' => 219.99, 'desc' => 'La sneaker qui a tout changé. Un classique indémodable porté par Michael Jordan lui-même.', 'cat' => 1],
            ['nom' => 'Adidas Harden Vol. 7', 'prix' => 149.99, 'desc' => 'Signature de James Harden, parfaite pour les guards cherchant réactivité et style sur le terrain.', 'cat' => 1],
            ['nom' => 'Nike LeBron 21', 'prix' => 199.99, 'desc' => 'La chaussure signature de LeBron James avec un soutien maximal pour les pivots dominants.', 'cat' => 1],
            ['nom' => 'Adidas Stan Smith', 'prix' => 89.99, 'desc' => 'Le classique absolu du lifestyle. Simple, élégant, intemporel. Un must-have dans tout dressing.', 'cat' => 2],
            ['nom' => 'Nike Air Force 1', 'prix' => 109.99, 'desc' => "Depuis 1982, l'Air Force 1 est une icône culturelle portée par les artistes et les fashionistas.", 'cat' => 2],
            ['nom' => 'New Balance 574', 'prix' => 99.99, 'desc' => 'Un design rétro avec un confort moderne. La 574 est la sneaker lifestyle par excellence.', 'cat' => 2],
            ['nom' => 'Converse Chuck Taylor', 'prix' => 69.99, 'desc' => "L'originale. La Chuck Taylor All Star est la chaussure la plus vendue de tous les temps.", 'cat' => 2],
            ['nom' => 'Vans Old Skool', 'prix' => 79.99, 'desc' => 'La première chaussure Vans avec la célèbre bande latérale. Un classique du skateboard depuis 1977.', 'cat' => 3],
            ['nom' => 'DC Shoes Pure', 'prix' => 74.99, 'desc' => 'Une chaussure de skate légère et durable, appréciée par les skateurs du monde entier.', 'cat' => 3],
            ['nom' => 'Nike SB Dunk Low', 'prix' => 119.99, 'desc' => "Conçue pour le skateboard, la SB Dunk Low est devenue l'une des sneakers les plus convoitées.", 'cat' => 3],
        ];

        foreach ($produits as $p) {
            $produit = new Produit();
            $seed = substr(hash('sha256', $p['nom']), 0, 20);
            $produit->setNom($p['nom'])
                ->setPrix(number_format((float) $p['prix'], 2, '.', ''))
                ->setDescription($p['desc'])
                ->setCategorie($cats[$p['cat']])
                ->setImage('https://picsum.photos/seed/'.$seed.'/400/280');
            $manager->persist($produit);
        }

        // ADMIN
        $admin = new User();
        $admin->setEmail('admin@sneakers.fr')->setNom('Admin')->setPrenom('Super')
            ->setAdresse('1 rue de la Paix')->setCodePostal('75001')->setVille('Paris')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // USER
        $user = new User();
        $user->setEmail('user@sneakers.fr')->setNom('Dupont')->setPrenom('Jean')
            ->setAdresse('42 avenue Victor Hugo')->setCodePostal('69001')->setVille('Lyon')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->hasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        $manager->flush();
    }
}