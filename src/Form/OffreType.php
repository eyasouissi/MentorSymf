<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class OffreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom_offre', TextType::class, [
            'label' => 'Name',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'The name cannot be empty.',
                ]),
                new Assert\Length([
                    'min' => 3,
                    'max' => 50,
                    'minMessage' => 'The name should be at least {{ limit }} characters long.',
                    'maxMessage' => 'The name cannot be longer than {{ limit }} characters.',
                ]),
            ],
            'empty_data' => '',  // Ajout de la valeur par défaut si le champ est vide
        ])
        
        ->add('image_offre', FileType::class, [
            'label' => 'Image',
            'required' => false,  // Il est toujours possible de laisser vide ce champ
            'attr' => ['class' => 'form-control'],
            'mapped' => false,  // Empêche le mapping direct à l'entité
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Veuillez télécharger une image.',
                    'groups' => ['Default'],  // Cette contrainte s'appliquera seulement si l'image est fournie
                ]),
                new Assert\Image([
                    'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                    'maxSize' => '2M',
                    'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF)',
                    'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo',
                ]),
            ],
        ])
        ->add('prix', IntegerType::class, [
            'label' => 'Price',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'The price cannot be empty.',
                ]),
                new Assert\GreaterThan([
                    'value' => 0,
                    'message' => 'The price must be greater than 0.',
                ]),
            ],
        ])
        ->add('date_debut', DateType::class, [
            'label' => 'Start date',
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please provide a start date.',
                ]),
                new Assert\GreaterThanOrEqual([
                    'value' => 'today',
                    'message' => 'The start date must be today or in the future.',
                ]),
            ],
        ])
        ->add('date_fin', DateType::class, [
            'label' => 'End date',
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Please provide an end date.',
                ]),
                new Assert\GreaterThan([
                    'propertyPath' => 'parent.all[date_debut].data',
                    'message' => 'The end date must be after the start date.',
                ]),
            ],
            'input' => 'datetime',  // Assurez-vous que le formulaire attend un DateTimeInterface
            'model_timezone' => 'UTC',  // S'il y a un problème de fuseau horaire
            'view_timezone' => 'UTC',  // Affichage en UTC
            'empty_data' => null,  // Assurez-vous que null est utilisé si aucune valeur n'est fournie
        ])
        
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control'],
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Description cannot be empty.',
                ]),
                new Assert\Length([
                    'min' => 5,
                    'max' => 100,
                    'minMessage' => 'Description should be at least {{ limit }} characters long.',
                    'maxMessage' => 'Description cannot be longer than {{ limit }} characters.',
                ]),
            ],
        ])
    ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
