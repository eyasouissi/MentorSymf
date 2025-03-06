<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;


class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [new NotBlank(['message' => 'Title cannot be empty'])],
            ])
            ->add('description_project', TextareaType::class, [
                'attr' => ['class' => 'editor'],
                'constraints' => [
                    new Length([
                        'min' => 20,
                        'minMessage' => 'Description must contain atleast 20 character',
                    ]),
                ],
            ])
            ->add('difficulte', ChoiceType::class, [
                'choices' => [
                    'Facile' => 'facile',
                    'Moyen' => 'moyen',
                    'Difficile' => 'difficile',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Difficulty',
            ])
            ->add('date_limite', DateTimeType::class, [
                'widget' => 'single_text',
                'attr' => ['min' => (new \DateTime())->format('Y-m-d\TH:i')],
            ])
            ->add('fichier_pdf', FileType::class, [
                'label' => 'Upload a PDF file',
                'mapped' => true,  
                'required' => true,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['application/pdf'],
                        'mimeTypesMessage' => 'Please upload a valid PDF file',
                    ])
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}