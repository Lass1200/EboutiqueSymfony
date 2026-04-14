<?php

namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['maxlength' => 100],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug (URL)',
                'required' => false,
                'help' => 'Laissez vide : le slug sera généré à partir du nom (minuscules et tirets).',
                'attr' => ['maxlength' => 120],
            ])
        ;

        
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $data = $event->getData();
            if (!\is_array($data)) {
                return;
            }
            $nom = trim((string) ($data['nom'] ?? ''));
            $slug = trim((string) ($data['slug'] ?? ''));
            if ($nom !== '' && $slug === '') {
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $nom));
                $data['slug'] = trim($slug, '-');
                $event->setData($data);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
