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
           
            'empty_data' => '',  // Ajout de la valeur par défaut si le champ est vide
        ])
        
        ->add('image_offre', FileType::class, [
            'label' => 'Image',
            'required' => false,  // Il est toujours possible de laisser vide ce champ
            'attr' => ['class' => 'form-control'],
            'mapped' => false,  // Empêche le mapping direct à l'entité

        ])
        ->add('prix', IntegerType::class, [
            'label' => 'Price',
            'attr' => ['class' => 'form-control'],
           
        ])
        ->add('date_debut', DateType::class, [
            'label' => 'Start date',
            'widget' => 'single_text',
           
        ])
        ->add('date_fin', DateType::class, [
            'label' => 'End date',
            'widget' => 'single_text',
            'attr' => ['class' => 'form-control'],
            
            'input' => 'datetime',  // Assurez-vous que le formulaire attend un DateTimeInterface
            'model_timezone' => 'UTC',  // S'il y a un problème de fuseau horaire
            'view_timezone' => 'UTC',  // Affichage en UTC
            'empty_data' => null,  // Assurez-vous que null est utilisé si aucune valeur n'est fournie
        ])
        
        ->add('description', TextareaType::class, [
            'label' => 'Description',
            'attr' => ['class' => 'form-control'],
            
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
