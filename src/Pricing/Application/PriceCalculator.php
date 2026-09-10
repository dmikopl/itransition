<?php

declare(strict_types=1);

namespace App\Pricing\Application;

use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\PricingResult;

final class PriceCalculator
{
    public function __construct(
        private readonly PricingRuleRepositoryInterface $ruleRepository,
        private readonly PricingEngine $pricingEngine,
    ) {
    }

    public function calculate(PricingContext $context): PricingResult
    {
        $rules = $this->ruleRepository->findCandidates($context);

        return $this->pricingEngine->calculate($context, $rules);
    }
}