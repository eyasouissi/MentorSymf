<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType; 
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
        ->add('image', FileType::class, [
            'label' => 'Project Image',
            'required' => false,
            'mapped' => false, // Do not map to the entity directly
            'attr' => [
                'accept' => 'image/*', // Only allow image files
            ]
        
        ])
            ->add('titre', TextType::class, [
                'label' => 'Title '],)

            ->add('description_project', TextType::class, [
                'label' => 'Project description '],)
                ->add('fichier_pdf', FileType::class, [
                    'label' => 'Upload a PDF file ',
                    'mapped' => true,
                    'required' => true,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => ['application/pdf'],
                        ])
                    ],
                ])
                ->add('difficulte', IntegerType::class, [
                    'required' => false, 
                ])
                ->add('date_limite', DateTimeType::class, [
                    'label' => 'Limit date',
                    'widget' => 'single_text',
                    'attr' => ['min' => (new \DateTime())->format('Y-m-d\TH:i')],
                ])
                ;
            
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}