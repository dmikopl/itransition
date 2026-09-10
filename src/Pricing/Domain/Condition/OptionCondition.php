<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Condition;

use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\PricingContext;

final readonly class OptionCondition implements PricingConditionInterface
{
    public function __construct(
        private ActivityOption $option,
    ) {
    }

    public function matches(PricingContext $context): bool
    {
        return $context->availability()->option() === $this->option;
    }
}
