<?php
namespace App\Form;

use App\Entity\User;
use App\Entity\Project;
use App\Entity\GroupStudent;
use Symfony\Component\Form\AbstractType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class GroupType extends AbstractType
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom_group')
            ->add('description_group')
            ->add('image', FileType::class, [
                'label' => 'Group Image',
                'required' => true,
                'mapped' => false, // Do not map to the entity directly
                'attr' => [
                    'accept' => 'image/*', // Allow only image files
                ],
            ])
            ->add('date_meet', DateTimeType::class, [
                'widget' => 'single_text', // Use a single text field for date and time
                'html5' => true, // Use HTML5 format
                'attr' => [
                    'class' => 'form-control form-control-lg flatpickr',
                    'placeholder' => 'Select date and time...',
                ],
            ])
            ->add('projects', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'titre', // Display the project title
                'multiple' => true, // Allow multiple selections
                'expanded' => false, // Use a dropdown
                'required' => false, // Optional
            ]);

        // Add the members field only if the form is in edit mode
        if ($options['is_edit']) {
            $builder->add('members', EntityType::class, [
                'class' => User::class, // Use the User entity
                'choice_label' => 'name', // Display the user's name (or another property)
                'multiple' => true, // Allow multiple selections
                'expanded' => false, // Use a dropdown
                'required' => false, // Optional
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupStudent::class,
            'is_edit' => false, // Default to false (add mode)
        ]);
    }
}