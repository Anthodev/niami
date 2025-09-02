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
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateReportForm extends AbstractType
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
            ->add('is60FpsPortable', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.portable.is_60fps',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('hasStableFrameratePortable', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.portable.has_stable_framerate',
                    [],
                    'report',
                ),
                'empty_data' => true,
                'data' => true,
                'required' => false,
            ])
            ->add('hasResolutionImprovedPortable', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.portable.has_resolution_improved',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('isNativeResolutionPortable', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.portable.is_native_resolution',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('is60FpsDocked', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.tv.is_60fps',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('hasStableFramerateDocked', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.tv.has_stable_framerate',
                    [],
                    'report',
                ),
                'empty_data' => true,
                'data' => true,
                'required' => false,
            ])
            ->add('hasResolutionImprovedDocked', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.tv.has_resolution_improved',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('isNativeResolutionDocked', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.tv.is_native_resolution',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('hasImprovedLoadingTimes', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.misc.has_improved_loading_times',
                    [],
                    'report',
                ),
                'empty_data' => true,
                'data' => true,
                'required' => false,
            ])
            ->add('isSwitch2Edition', CheckboxType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.misc.is_switch_2_editon',
                    [],
                    'report',
                ),
                'empty_data' => false,
                'data' => false,
                'required' => false,
            ])
            ->add('gameStatus', EnumType::class, [
                'label' => $this->translator->trans(
                    'add_report.form.misc.game_status',
                    [],
                    'report',
                ),
                'class' => ReportGameStatusEnum::class,
                'choice_attr' => [
                    ReportGameStatusEnum::OK->name => ['selected' => true],
                ],
                'empty_data' => ReportGameStatusEnum::OK->value,
                'required' => true,
            ])
            ->add('gameId', HiddenType::class, [
                'required' => true,
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('gameSlug', HiddenType::class, [
                'required' => true,
                'constraints' => [new Assert\NotBlank()],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateReportFormInputDto::class,
        ]);
    }
}
