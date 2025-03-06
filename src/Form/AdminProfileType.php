<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
//to edit an admin
class AdminProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
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
            ->add('age', TextType::class, [
                'label' => 'Age',
                'attr' => ['class' => 'form-control']
            ])
            ->add('country', CountryType::class, [
                'placeholder' => 'Choose your country',
            ])
            ->add('speciality', TextType::class, [
                'label' => 'Speciality',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('pfp', FileType::class, [
                'label' => 'profile picture',
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
            ])
            ->add('bg', FileType::class, [
                'label' => 'background picture',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
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

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'userType' => null,
        ]);
    }
}
