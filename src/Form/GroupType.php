<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\GroupStudent;
use Symfony\Component\Form\AbstractType;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('members', EntityType::class, [
                'class' => User::class,
                'query_builder' => function (UserRepository $repo) {
                    // Using the method defined in the repository to find users with 'ROLE_STUDENT'
                    return $repo->findByRoleStudent();
                },
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false, // Use true for checkboxes, false for a dropdown
            ])
            ->add('description_group')
            ->add('image', FileType::class, [
                'label' => 'Group Image',
                'required' => true,
                'mapped' => false, // Do not map to the entity directly
                'attr' => [
                    'accept' => 'image/*', // Allow only image files
                ]
            ])
            ->add('date_meet', null, [
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GroupStudent::class,
        ]);
    }
}
