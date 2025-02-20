<?php
namespace App\Form;

use App\Entity\Annonce;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnonceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('image_a', FileType::class, [
                'label' => 'Image',
                'mapped' => false,  // This will not map directly to the entity
                'required' => false, // Image is optional
            ])
            ->add('titre_a', TextType::class, [
                'label' => 'Title',
            ])
            ->add('description_a', TextareaType::class, [
                'label' => 'Description',
            ])
            // Do not add 'date_a' and 'user' fields here, as they are automatically set in the controller
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Annonce::class,
        ]);
    }
}
