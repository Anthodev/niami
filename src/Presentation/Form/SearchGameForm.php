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
            'label' => 'Search the game:',
            'required' => false,
            'constraints' => [
                new Assert\Length([
                    'min' => 3,
                    'minMessage' => 'The search query must be at least {{ limit }} characters.',
                    'max' => 100,
                    'maxMessage' => 'The search query can\'t be above {{ limit }} characters.',
                ]),
            ],
            'attr' => [
                'placeholder' => 'Type at least 3 characters...',
                'autocomplete' => 'off',
            ],
        ]);
    }
}
