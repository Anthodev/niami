<?php

declare(strict_types=1);

namespace App\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchGameForm extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->add('game', TextType::class, [
            'label' => 'Rechercher un jeu',
            'required' => false,
            'constraints' => [
                new Assert\Length([
                    'min' => 3,
                    'minMessage' => 'Le terme de recherche doit contenir au moins {{ limit }} caractères.',
                    'max' => 100,
                    'maxMessage' => 'Le terme de recherche ne peut pas dépasser {{ limit }} caractères.',
                ]),
            ],
            'attr' => [
                'placeholder' => 'Tapez au moins 3 caractères...',
                'autocomplete' => 'off',
            ],
        ]);
    }
}
