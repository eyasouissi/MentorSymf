<?php

namespace App\Form;

use App\Entity\Profile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;


class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'First Name',
                'attr' => ['class' => 'form-control'],
                'help' => 'Please enter your first name',
                'error_bubbling' => true, // This will display the error below the field if any
            ])
            ->add('lastname', TextType::class, [
                'label' => 'Last Name',
                'attr' => ['class' => 'form-control'],
                'help' => 'Please enter your last name',
                'error_bubbling' => true,
            ])
            ->add('profileimage', FileType::class, [
                'label' => 'Profile Image',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Upload an image for your profile (Optional)',
                'error_bubbling' => true,
            ])
            ->add('bio', TextareaType::class, [
                'label' => 'Biography',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 5],
                'help' => 'Tell us something about yourself',
                'error_bubbling' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'attr' => ['class' => 'form-control'],
                'help' => 'Enter your email address',
                'error_bubbling' => true,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'attr' => ['class' => 'form-control'],
                'help' => 'Choose a secure password',
                'error_bubbling' => true,
            ])
            ->add('accountname', TextType::class, [
                'label' => 'Account Name',
                'attr' => ['class' => 'form-control'],
                'help' => 'Enter a unique username',
                'error_bubbling' => true,
            ])
            ->add('datecreation', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Creation Date',
                'attr' => ['class' => 'form-control'],
                'help' => 'The date the account was created',
                'error_bubbling' => true,
            ])
            ->add('status', TextType::class, [
                'label' => 'Status',
                'attr' => ['class' => 'form-control'],
                'help' => 'Current status of your account',
                'error_bubbling' => true,
            ])
            ->add('role', TextType::class, [
                'label' => 'Role',
                'attr' => ['class' => 'form-control'],
                'help' => 'Define the user role (admin, user, etc.)',
                'error_bubbling' => true,
            ])
            ->add('usertype', TextType::class, [
                'label' => 'User Type',
                'attr' => ['class' => 'form-control'],
                'help' => 'Specify the type of user',
                'error_bubbling' => true,
            ])
            ->add('lastlogin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Last Login',
                'attr' => ['class' => 'form-control'],
                'help' => 'When the user last logged in',
                'error_bubbling' => true,
            ])
            ->add('genre', TextType::class, [
                'label' => 'Gender',
                'attr' => ['class' => 'form-control'],
                'help' => 'Specify your gender',
                'error_bubbling' => true,
            ])
            ->add('sessiontocken', TextType::class, [
                'label' => 'Session Token',
                'attr' => ['class' => 'form-control'],
                'help' => 'The token for your session',
                'error_bubbling' => true,
            ])
            ->add('sessionexpiration', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Session Expiration',
                'attr' => ['class' => 'form-control'],
                'help' => 'When your session will expire',
                'error_bubbling' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Upload an image (optional)',
                'error_bubbling' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Profile::class,
        ]);
    }
}
