<?php

declare(strict_types=1);

namespace App\Form;

use App\Form\Dto\TicketCategoryInput;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<TicketCategoryInput>
 */
final class TicketCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [new Assert\NotBlank(message: 'Ticket category name is required.')],
                'attr' => ['required' => true],
            ])
            ->add('price', NumberType::class, [
                'scale' => 2,
                'html5' => true,
                'constraints' => [
                    new Assert\NotNull(message: 'Ticket price is required.'),
                    new Assert\Positive(message: 'Ticket price must be greater than 0.'),
                ],
                'attr' => [
                    'required' => true,
                    'min' => '0.01',
                    'step' => '0.01',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TicketCategoryInput::class,
        ]);
    }
}
