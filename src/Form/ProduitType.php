<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Produit;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['maxlength' => 150],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('prix', TextType::class, [
                'label' => 'Prix (€)',
                'help' => 'Nombre avec ou sans décimales, ex. 129 ou 129.99',
                'attr' => ['placeholder' => '99.99'],
            ])
            ->add('image', TextType::class, [
                'label' => 'Image',
                'required' => false,
                'help' => 'URL https://… ou nom de fichier dans public/images (ex. nike-air.jpg ou images/dossier/photo.png).',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
                'required' => false,
                'label' => 'Catégorie',
                'placeholder' => '— Aucune —',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
