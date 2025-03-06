<?php

namespace App\Form;

use App\Entity\ProhibitedWord;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProhibitedWordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('word', TextType::class, [
                'label' => 'Prohibited Term',
                'help' => 'The word or phrase to block',
                'attr' => ['class' => 'form-control']
            ])
            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Profanity' => 'profanity',
                    'Personal Info' => 'personal_info',
                    'Spam' => 'spam',
                    'Other' => 'other'
                ],
                'attr' => ['class' => 'form-select']
            ])
            ->add('severity', ChoiceType::class, [
                'choices' => [
                    'Level 1' => 1,
                    'Level 2' => 2,
                    'Level 3' => 3,
                    'Level 4' => 4,
                    'Level 5' => 5
                ],
                'attr' => ['class' => 'form-select']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProhibitedWord::class,
        ]);
    }
}