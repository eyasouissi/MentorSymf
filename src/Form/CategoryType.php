<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', TextType::class, [
            'label' => 'Nom de la catégorie',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Le nom de la catégorie est obligatoire.',
                ]),
                new Assert\Length([
                    'min' => 3,
                    'max' => 255,
                    'minMessage' => 'Le nom de la catégorie doit comporter au moins {{ limit }} caractères.',
                    'maxMessage' => 'Le nom de la catégorie ne peut pas dépasser {{ limit }} caractères.',
                ])
            ],
        ])
        
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ])
        ->add('created_at', DateTimeType::class, [
            'label' => 'Date de création',
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control'],
            'disabled' => true,  // Empêche la modification
        ])
        ->add('is_active', CheckboxType::class, [
            'label' => 'Actif',
            'required' => false,
            'attr' => ['class' => 'form-checkbox'],
            'property_path' => 'is_active',  // Mappe la propriété is_active dans l'entité
        ])
        
        ->add('icon', FileType::class, [
            'label' => 'Icône ou Vidéo (fichier image ou vidéo)',
            'required' => false,
            'attr' => ['class' => 'form-control'],
            'mapped' => false,  // Important si tu ne veux pas que ce champ soit directement lié à l'entité
            'constraints' => [
                new Assert\File([
                    'mimeTypes' => [
                        'image/jpeg', 'image/png', 'image/gif',  // Types d'images
                        'video/mp4', 'video/webm', 'video/ogg',  // Types de vidéos
                    ],
                    'maxSize' => '5M',  // Limite de taille du fichier (ajuste selon tes besoins)
                ]),
            ],
        ]);
        
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
