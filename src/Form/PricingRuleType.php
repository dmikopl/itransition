<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Dto\PricingRuleInput;
use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<PricingRuleInput>
 */
final class PricingRuleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('priority', IntegerType::class, [
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('adjustmentType', ChoiceType::class, [
                'choices' => [
                    'Percentage discount' => 'percentage_discount',
                    'Percentage surcharge' => 'percentage_surcharge',
                    'Fixed discount (cents)' => 'fixed_discount',
                ],
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('adjustmentValue', IntegerType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Positive(),
                ],
                'help' => 'Percent (1-100) or amount in USD cents for fixed discount.',
            ])
            ->add('activity', EnumType::class, [
                'class' => Activity::class,
                'choice_label' => 'label',
                'required' => false,
                'placeholder' => 'Any activity',
            ])
            ->add('option', EnumType::class, [
                'class' => ActivityOption::class,
                'choice_label' => 'label',
                'required' => false,
                'placeholder' => 'Any option',
            ])
            ->add('dateFrom', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('dateTo', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'required' => false,
            ])
            ->add('daysOfWeek', ChoiceType::class, [
                'choices' => [
                    'Monday' => 1,
                    'Tuesday' => 2,
                    'Wednesday' => 3,
                    'Thursday' => 4,
                    'Friday' => 5,
                    'Saturday' => 6,
                    'Sunday' => 7,
                ],
                'multiple' => true,
                'expanded' => true,
                'required' => false,
            ])
            ->add('minAdvanceDays', IntegerType::class, [
                'required' => false,
                'constraints' => [new Assert\PositiveOrZero()],
            ])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PricingRuleInput::class,
            'constraints' => [
                new Assert\Callback(static function (PricingRuleInput $input, ExecutionContextInterface $context): void {
                    if (null !== $input->dateFrom && null !== $input->dateTo && $input->dateFrom > $input->dateTo) {
                        $context->buildViolation('Date from must be before or equal to date to.')
                            ->atPath('dateTo')
                            ->addViolation();
                    }

                    if ((null === $input->dateFrom) xor (null === $input->dateTo)) {
                        $context->buildViolation('Provide both date from and date to, or leave both empty.')
                            ->atPath('dateFrom')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
