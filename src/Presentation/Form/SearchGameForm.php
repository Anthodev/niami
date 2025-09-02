<?php

declare(strict_types=1);

namespace App\Presentation\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;

class SearchGameForm extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {}

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder->add('game', TextType::class, [
            'label' => $this->translator->trans('form.label', [], 'search'),
            'required' => false,
            'constraints' => [
                new Assert\Length(
                    min: 3,
                    max: 100,
                    minMessage: $this->translator->trans(
                        'form.constraint.min_length_message',
                        ['limit' => '{{ limit }}'],
                        'search',
                    ),
                    maxMessage: $this->translator->trans(
                        'form.constraint.max_length_message',
                        ['limit' => '{{ limit }}'],
                        'search',
                    ),
                ),
            ],
            'attr' => [
                'placeholder' => $this->translator->trans('form.placeholder', [], 'search'),
                'autocomplete' => 'off',
            ],
        ]);
    }
}
