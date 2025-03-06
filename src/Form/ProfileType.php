<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use App\Entity\User;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => ['class' => 'form-control']
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Bio',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('pfp', FileType::class, [
                'label' => 'Profile picture',
                'mapped' => false, 
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/jpg'],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPG, JPEG, PNG).',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);
            $builder->add('bg', FileType::class, [
                'label' => 'background picture',
                'required' => false,  // Background image is optional
                'mapped' => false,    // No entity mapping needed
                'attr' => ['class' => 'form-control'],
            ]);
        // Tutor-specific fields
        if ($options['userType'] === 'tutor') {
            $builder
                ->add('speciality', TextType::class, [
                    'label' => 'Speciality',
                    'required' => false,
                    'attr' => ['class' => 'form-control']
                ])
                ->add('diplome', FileType::class, [
                    'label' => 'Diploma (PDF ou Word)',
                    'mapped' => false,
                    'required' => false,
                    'constraints' => [
                        new File([
                            'maxSize' => '5M',
                            'mimeTypes' => [
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            ],
                            'mimeTypesMessage' => 'Seuls les fichiers PDF et Word sont acceptés.',
                        ])
                    ],
                    'attr' => ['class' => 'form-control']
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'userType' => null, // Optional user type for tutor fields
        ]);
    }
}