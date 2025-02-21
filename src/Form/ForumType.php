<?php
namespace App\Form;

use App\Entity\Forum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class ForumType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Define your predefined topics
        $topics = [
            'General' => 'general',
            'Review' => 'review',
            'Feedback' => 'feedback',
            'Education' => 'education',
            'Professional' => 'professional',
        ];

        $builder
            ->add('title', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'Title should not be blank']),
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'full', // Enables all tools (bold, size, font, etc.)
                    'language' => 'en',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Description should not be blank']),
                    new Length(['max' => 255, 'maxMessage' => 'Description cannot be longer than 255 characters']),
                ],
                'attr' => ['class' => 'ckeditor form-control'],
            ])
            ->add('isPublic', CheckboxType::class, [
                'required' => false,
            ])
            ->add('createdAt', DateTimeType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(), // Set default value to current date and time
            ])
            ->add('updatedAt', DateTimeType::class, [
                'widget' => 'single_text',
                'data' => new \DateTime(), // Set default value to current date and time
                'constraints' => [
                    new GreaterThanOrEqual([
                        'propertyPath' => 'parent.all[createdAt].data',
                        'message' => 'Updated date cannot be before created date.',
                    ]),
                ],
            ])
            ->add('topics', ChoiceType::class, [
                'choices' => $topics, // Use the predefined list of topics
                'constraints' => [
                    new NotBlank(['message' => 'Please select a topic']),
                ],
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Forum::class,
        ]);
    }
}
