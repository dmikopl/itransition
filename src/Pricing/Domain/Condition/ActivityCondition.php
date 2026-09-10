<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Condition;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Model\PricingContext;

final readonly class ActivityCondition implements PricingConditionInterface
{
    public function __construct(
        private Activity $activity,
    ) {
    }

    public function matches(PricingContext $context): bool
    {
        return $context->availability()->activity() === $this->activity;
    }
}
