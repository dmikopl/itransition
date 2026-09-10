<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\CalculatorType;
use App\Form\Dto\CalculatorInput;
use App\Form\Dto\TicketCategoryInput;
use App\Pricing\Application\PriceCalculator;
use App\Pricing\Domain\Model\Availability;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\TicketCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PricingController extends AbstractController
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {
    }

    #[Route('/', name: 'pricing_calculate', methods: ['GET', 'POST'])]
    public function calculate(Request $request): Response
    {
        $input = new CalculatorInput();
        $form = $this->createForm(CalculatorType::class, $input);
        $form->handleRequest($request);

        $result = null;

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var CalculatorInput $input */
            $input = $form->getData();

            $ticketCategories = array_map(
                static fn (TicketCategoryInput $ticket): TicketCategory => new TicketCategory(
                    $ticket->name,
                    Money::fromCents((int) round($ticket->price * 100)),
                ),
                $input->ticketCategories,
            );

            $context = new PricingContext(
                availability: new Availability(
                    activity: $input->activity,
                    option: $input->option,
                    dateTime: $input->activityDate,
                    ticketCategories: $ticketCategories,
                ),
                bookingDate: $input->bookingDate,
            );

            $result = $this->priceCalculator->calculate($context);
        }

        return $this->render('pricing/calculate.html.twig', [
            'form' => $form,
            'result' => $result,
        ]);
    }
}