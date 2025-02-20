<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TutorRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];
        $plainPasswordOptions = [
            'mapped' => true,
            'attr' => ['autocomplete' => 'new-password'],
        ];

        if (!$isEdit) {
            $plainPasswordOptions['constraints'] = [
                new NotBlank(['message' => 'Password cannot be blank.']),
                new Length([
                    'min' => 6,
                    'minMessage' => 'Password must be at least 6 characters long.',
                ]),
            ];
        } else {
            $plainPasswordOptions['required'] = false;
            $plainPasswordOptions['attr']['placeholder'] = 'Leave blank if unchanged';
        }

        $builder
        ->add('email', EmailType::class)
        ->add('name', TextType::class)
        ->add('age', IntegerType::class, [
            'required' => false,
        ])
        ->add('gender', ChoiceType::class, [
            'choices' => [
                'Female' => 'female',
                'Male' => 'male',
            ],
            'placeholder' => 'Choose your gender',
        ])
        ->add('country', CountryType::class, [
            'placeholder' => 'Choose your country',
        ])
            ->add('diplome', FileType::class, [
                'required' => true,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],
            ])
            ->add('speciality', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter your speciality',
                    ]),
                ],
            ])
          
            ->add('plainPassword', PasswordType::class, $plainPasswordOptions);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
            'validation_groups' => ['Default', 'Registration'],
        ]);
    }
}
