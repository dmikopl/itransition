<?php

declare(strict_types=1);

namespace App\Controller;

use App\Form\Dto\PricingRuleInput;
use App\Form\PricingRuleType;
use App\Pricing\Infrastructure\Doctrine\PricingRuleEntity;
use App\Pricing\Infrastructure\Doctrine\PricingRuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PricingRuleController extends AbstractController
{
    public function __construct(
        private readonly PricingRuleRepository $ruleRepository,
    ) {
    }

    #[Route('/rules', name: 'pricing_rules_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pricing/rule_index.html.twig', [
            'rules' => $this->ruleRepository->findAllOrdered(),
        ]);
    }

    #[Route('/rules/new', name: 'pricing_rules_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $input = new PricingRuleInput();
        $form = $this->createForm(PricingRuleType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PricingRuleInput $input */
            $input = $form->getData();

            $entity = new PricingRuleEntity(
                name: $input->name,
                priority: $input->priority,
                adjustmentType: $input->adjustmentType,
                adjustmentValue: (int) $input->adjustmentValue,
                activity: $input->activity?->value,
                activityOption: $input->option?->value,
                dateFrom: $input->dateFrom,
                dateTo: $input->dateTo,
                daysOfWeek: $input->daysOfWeek === [] ? null : array_values($input->daysOfWeek),
                minAdvanceDays: $input->minAdvanceDays,
            );

            $this->ruleRepository->save($entity);

            return $this->redirectToRoute('pricing_rules_index');
        }

        return $this->render('pricing/rule_new.html.twig', [
            'form' => $form,
        ]);
    }
}