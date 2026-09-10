<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Condition;

use App\Pricing\Domain\Model\PricingContext;

interface PricingConditionInterface
{
    public function matches(PricingContext $context): bool;
}