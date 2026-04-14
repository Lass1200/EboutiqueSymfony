– Projet E-boutique Symfony
Description

Ce projet est une application web développée avec le framework Symfony.
Il s’agit d’une e-boutique permettant aux utilisateurs de naviguer parmi des produits, gérer un panier et effectuer une commande.

Fonctionnalités
Authentification
Connexion (Login) : OK
Inscription avec contrôle de majorité (date de naissance) : Légers bugs

Gestion du profil
Mise à jour du profil utilisateur : OK
Gestion complète des utilisateurs (administration, suppression, etc.) : Non implémentée (hors scope)

Navigation et produits
Parcours par catégorie : OK
Parcours des articles : OK
Ajout d’un nouveau type d’article : Syntaxe en place mais non fonctionnelle

Catégories
Ajout d’une nouvelle catégorie : OK

Panier
Mise au panier : OK
Ajustement des quantités : Légers bugs
Calcul du prix total : Légers bugs

Commande
Validation de commande avec message de confirmation : OK
Limitations connues
Absence de gestion du stock
Aucun système de paiement réel
Gestion utilisateur limitée (hors inscription et modification)
Possibles incohérences dans le calcul du panier

Technologies utilisées
PHP / Symfony
Doctrine (ORM)
Twig (templates)
MySQL (base de données)

Installation
git clone <repository_url>
cd projet
composer install
symfony server:start

Configurer ensuite le fichier .env pour la connexion à la base de données.
