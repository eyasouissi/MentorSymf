<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints as Assert;

class StudentRegistrationType extends AbstractType
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
                'required' => true,  // Make it obligatory
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Age is required.']),
                    new Assert\Range([
                        'min' => 18,
                        'max' => 70,
                        'notInRangeMessage' => 'Age must be between 18 and 70.',
                    ]),
                ],
            ])
            
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Male' => 'male',
                    'Female' => 'female',
                ],
                'expanded' => true,  // Displays as radio buttons
                'multiple' => false, // Ensures only one selection
            ])

            ->add('country', CountryType::class, [
                'placeholder' => 'Choose your country',
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
