<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;

class PaiementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $options['data'];  
        $isEditMode = $data && $data->getCardNum(); 
        $builder
            ->add('id_user')
            ->add('id_offre')
            ->add('yourEmail', EmailType::class, [
                'label' => 'yourEmail',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Email is required.']),
                    new Assert\Email(['message' => 'Please enter a valid email address.']),
                ]
            ])
            
            ->add('Date_expiration', DateType::class, [
                'label' => 'Date d\'expiration',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'The expiration date must be in the future.',
                    ]),
                ]
            ]);

            if (!$isEditMode) {
                $builder->add('card_num');
            }


        $builder->add('cvv', IntegerType::class, [
            'label' => 'Code de sécurité (CVV)',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Le CVV est requis.']),
                new Assert\Length([
                    'min' => 3,
                    'max' => 4,
                    'exactMessage' => 'The CVV must contain exactly 3 or 4 digits.',
                ]),
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
