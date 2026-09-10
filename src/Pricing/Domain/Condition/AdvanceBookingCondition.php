<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Condition;

use App\Pricing\Domain\Model\PricingContext;

final readonly class AdvanceBookingCondition implements PricingConditionInterface
{
    public function __construct(
        private int $minimumDays,
    ) {
        if ($this->minimumDays < 0) {
            throw new \InvalidArgumentException('Minimum advance days cannot be negative.');
        }
    }

    public function matches(PricingContext $context): bool
    {
        return $context->daysInAdvance() >= $this->minimumDays;
    }
}