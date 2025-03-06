<?php
// src/Form/LevelType.php

namespace App\Form;

use App\Entity\Level;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LevelType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour le nom du niveau
            ->add('name', TextType::class, [
                'label' => 'Level Name',
                'attr' => ['class' => 'form-control']
            ])
            
            // Champ pour les fichiers
            ->add('files', FileType::class, [
                'label' => 'Files',
                'multiple' => true, // Permet de sélectionner plusieurs fichiers
                'mapped' => false,  // Lier à une entité séparée pour les fichiers
                'attr' => ['class' => 'form-control', 'multiple' => 'multiple'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Level::class,
        ]);
    }
}
