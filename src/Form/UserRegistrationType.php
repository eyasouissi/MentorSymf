<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // User info fields
            
            ->add('name', TextType::class)
            ->add('age', IntegerType::class, [
                'required' => false,
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
            ->add('email', EmailType::class)
            ->add('plainPassword', PasswordType::class)
            
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Tutor' => 'ROLE_TUTOR',
                    'Student' => 'ROLE_USER',
                ],
                'multiple' => true, // Allow multiple selections (even if only one is selected)
                'expanded' => true, // Render as radio buttons
                'label' => 'Role',
                'data' => in_array('ROLE_TUTOR', $options['data']->getRoles()) ? ['ROLE_TUTOR'] : ['ROLE_USER'], // Ensure array value
            ])
            
            // Fields specific to tutors (only shown if selected role is "ROLE_TUTOR")
            ->add('diplome', FileType::class, [
                'label' => 'Diploma (Optional)',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
