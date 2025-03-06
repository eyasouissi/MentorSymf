<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\FormInterface;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Post Content',
                'attr' => ['placeholder' => 'Write your post here...'],
                'constraints' => [
                    new NotBlank(['message' => 'Content cannot be empty.']),
                    new Length(['max' => 2000, 'maxMessage' => 'Content cannot exceed 2000 characters.'])
                ]
            ])
            ->add('photos', FileType::class, [
                'label' => false, // This removes the label
                'multiple' => true,
                'mapped' => false,
                'required' => false,
                'attr' => ['accept' => 'image/*']
            ]);
            

        if ($options['edit_mode']) {
            $builder->add('remove_photos', CollectionType::class, [
                'entry_type' => CheckboxType::class,
                'entry_options' => [
                    'label' => false,
                    'required' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
                'label' => 'Remove Photos',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Disable HTML5 validation globally for this form
            'attr' => ['novalidate' => 'novalidate'],
            // Prevent Symfony from adding required attributes
            'render_required_attr' => false,
            'data_class' => Post::class,
            'edit_mode' => false,
            'existing_photos' => [],
            'validation_groups' => function (FormInterface $form) {
                $groups = ['Default'];
                if ($form->getConfig()->getOption('edit_mode')) {
                    $groups[] = 'edit';
                }
                return $groups;
            }
            
        ]);

        $resolver->setAllowedTypes('edit_mode', 'bool');
        $resolver->setAllowedTypes('existing_photos', 'array');
    }
}