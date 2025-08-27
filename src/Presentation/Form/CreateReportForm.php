<?php

declare(strict_types=1);

namespace App\Presentation\Form;

use App\Infrastructure\Enum\ReportGameStatusEnum;
use App\Presentation\Dto\CreateReportFormInputDto;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CreateReportForm extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('is60FpsPortable', CheckboxType::class, [
                'label' => 'Target 60fps?',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('hasStableFrameratePortable', CheckboxType::class, [
                'label' => 'Stable framerate? (mostly keeping maximum framerate)',
                'empty_data' => true,
                'data' => true,
                'required' => false,
            ])
            ->add('hasResolutionImprovedPortable', CheckboxType::class, [
                'label' => 'Resolution improved?*',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('isNativeResolutionPortable', CheckboxType::class, [
                'label' => 'Native resolution?**',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('is60FpsDocked', CheckboxType::class, [
                'label' => 'Target 60fps?',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('hasStableFramerateDocked', CheckboxType::class, [
                'label' => 'Stable framerate? (mostly keeping maximum framerate)',
                'empty_data' => true,
                'data' => true,
                'required' => false,
            ])
            ->add('hasResolutionImprovedDocked', CheckboxType::class, [
                'label' => 'Resolution improved?*',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('isNativeResolutionDocked', CheckboxType::class, [
                'label' => 'Native resolution?**',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('hasImprovedLoadingTimes', CheckboxType::class, [
                'label' => 'Improved loading times?',
                'empty_data' => true,
                'data' => true,
                'required' => false,
            ])
            ->add('isSwitch2Edition', CheckboxType::class, [
                'label' => 'Is this a "Switch 2 Edition"?',
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('gameStatus', EnumType::class, [
                'label' => 'How run the game on Switch 2 system?',
                'class' => ReportGameStatusEnum::class,
                'choice_attr' => [
                    ReportGameStatusEnum::OK->name => ['selected' => true],
                ],
                'empty_data' => ReportGameStatusEnum::OK->value,
                'required' => true,
            ])
            ->add('gameId', HiddenType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
            ->add('gameSlug', HiddenType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateReportFormInputDto::class,
        ]);
    }
}
