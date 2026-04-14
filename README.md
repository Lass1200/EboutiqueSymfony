README – Projet E-boutique Symfony Description

Ce projet est une application web développée avec le framework Symfony, simulant une e-boutique en ligne. Elle permet aux utilisateurs de parcourir des produits, gérer un panier et passer une commande.

Fonctionnalités Authentification Connexion (Login) : OK Inscription avec contrôle de majorité (date de naissance) : Légers bugs Gestion du profil Mise à jour du profil utilisateur : OK La gestion complète des utilisateurs (admin, suppression, etc.) n’est pas implémentée (hors scope) Navigation et Produits Parcours par catégorie : OK Parcours des articles : OK Ajout d’un nouveau type d’article : Syntaxe en place mais non fonctionnelle

Catégories Ajout d’une nouvelle catégorie : OK

Panier Mise au panier : OK Ajustement des quantités : Légers bugs Calcul du prix total : Légers bugs

Commande Validation de commande avec message de confirmation : OK Limitations connues Pas de gestion du stock Pas de système de paiement réel

Gestion utilisateur limitée (hors inscription et modification) Certains calculs du panier peuvent présenter des incohérences

Technologies utilisées PHP / Symfony Doctrine (ORM) Twig (templates) MySQL (base de données)

Installation git clone cd projet composer install symfony server:start
