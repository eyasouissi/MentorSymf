<?php
namespace App\Form;

use App\Entity\Evenement; // Adjust according to your entity
use App\Entity\Annonce; // Make sure this is imported as well
use Symfony\Bridge\Doctrine\Form\Type\EntityType; // Import EntityType
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titreE', TextType::class, [
                'label' => 'Title',
            ])
            ->add('descriptionE', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Start Date',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'End Date',
            ])
            ->add('annonce', EntityType::class, [
                'class' => Annonce::class, // Use the class constant instead of string
                'choice_label' => 'titre_a',
                'label' => 'Annonce associée',
            ])
            ->add('imageE', FileType::class, [
                'label' => 'Image',
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class, // Adjust according to your entity
        ]);
    }
}
