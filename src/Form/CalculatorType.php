<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Dto\CalculatorInput;
use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @extends AbstractType<CalculatorInput>
 */
final class CalculatorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('activity', EnumType::class, [
                'class' => Activity::class,
                'choice_label' => 'label',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('option', EnumType::class, [
                'class' => ActivityOption::class,
                'choice_label' => 'label',
                'constraints' => [new Assert\NotNull()],
            ])
            ->add('activityDate', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\NotNull(message: 'Activity date is required.')],
                'attr' => ['required' => true],
            ])
            ->add('bookingDate', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new Assert\NotNull(message: 'Booking date is required.')],
                'attr' => ['required' => true],
                'help' => 'Must be on or before the activity date.',
            ])
            ->add('ticketCategories', CollectionType::class, [
                'entry_type' => TicketCategoryType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'constraints' => [new Assert\Count(min: 1, minMessage: 'Add at least one ticket category.')],
                'error_bubbling' => false,
            ])
            ->add('calculate', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CalculatorInput::class,
            'constraints' => [new Assert\Callback($this->validateDates(...))],
        ]);
    }

    public function validateDates(CalculatorInput $input, ExecutionContextInterface $context): void
    {
        if (null === $input->activityDate || null === $input->bookingDate) {
            return;
        }

        if ($input->bookingDate > $input->activityDate) {
            $context->buildViolation('Booking date must be on or before the activity date.')
                ->atPath('bookingDate')
                ->addViolation();
        }
    }
}
