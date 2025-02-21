<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Post Content',
                'attr' => [
                    'placeholder' => 'Write your post here...',
                    // Ensure there's no 'required' attribute for HTML validation
                    'class' => 'form-control',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Content cannot be empty.',
                    ]),
                    new Length([
                        'max' => 1000,
                        'maxMessage' => 'Content cannot exceed 2000 characters.',
                    ]),
                ],
                // Ensure no required HTML5 validation
                'required' => false, 
                'attr' => ['data-parsley-required' => 'false'] // Disabling the field as required via any JS lib
            ])
            ->add('photo', FileType::class, [
                'label' => 'Photo (optional)',
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Create Post',
            ])
            ->add('remove_photo', CheckboxType::class, [
                'label' => 'Remove current photo',
                'required' => false,
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
