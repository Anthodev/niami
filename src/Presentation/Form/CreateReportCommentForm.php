<?php

declare(strict_types=1);

namespace App\Presentation\Form;

use App\Presentation\Dto\CreateReportCommentFormInputDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateReportCommentForm extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('comment', TextareaType::class, [
                'label' => $this->translator->trans(
                    'form.create.comment.label',
                    [],
                    'reportComment',
                ),
                'attr' => [
                    'placeholder' => $this->translator->trans(
                        'form.create.comment.placeholder',
                        [],
                        'reportComment',
                    ),
                ],
                'empty_data' => '',
                'required' => true,
            ])
            ->add('ip', HiddenType::class, [
                'required' => true,
            ])
            ->add('gameSlug', HiddenType::class, [
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateReportCommentFormInputDto::class,
        ]);
    }
}
