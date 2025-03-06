<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    { $isEdit = $options['is_edit'];

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
            ->add('name', TextType::class, ['label' => 'Name'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('plainPassword', PasswordType::class, $plainPasswordOptions)
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Tutor' => 'ROLE_TUTOR',
                    'Étudiant' => 'ROLE_USER',
                ],
                'multiple' => false,  // Only one choice allowed
                'expanded' => true,   // Display as radio buttons
                'label' => 'Rôles',
                'data' => $options['data']->getRoles()[0] ?? null, // Pre-select current role
                'mapped' => false,    // Prevents automatic mapping
            ])
            
            
            ->add('is_verified');


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_edit' => false,
        ]);
    }
}