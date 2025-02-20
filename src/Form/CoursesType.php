<?php
// src/Form/CoursesType.php

// src/Form/CoursesType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Courses;
use App\Form\LevelType;

class CoursesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Course Title',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Course Description',
                'required' => false,
                'attr' => ['class' => 'ckeditor'],  // Add a class for CKEditor
            ])
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Creation Date',
                'required' => false,
                'disabled' => true,
            ])
            ->add('category', ChoiceType::class, [
                'choices' => $options['categories'],
                'choice_label' => 'name',
                'placeholder' => 'Select a Category',
                'label' => 'Category',
            ])
            ->add('numberOfLevels', ChoiceType::class, [
                'label' => 'Number of Levels',
                'choices' => [
                    '1 level' => 1,
                    '2 levels' => 2,
                    '3 levels' => 3,
                    '4 levels' => 4,
                    '5 levels' => 5,
                ],
                'mapped' => false, 
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Courses::class,
            'categories' => [],
        ]);
    }
}
