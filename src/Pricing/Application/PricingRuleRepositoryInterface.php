<?php

declare(strict_types=1);

namespace App\Pricing\Application;

use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\PricingRule;

interface PricingRuleRepositoryInterface
{
    /**
     * @return list<PricingRule>
     */
    public function findCandidates(PricingContext $context): array;

    /**
     * @return list<PricingRule>
     */
    public function findAllOrdered(): array;
}