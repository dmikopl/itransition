<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Condition;

use App\Pricing\Domain\Model\PricingContext;
use DateTimeImmutable;

final readonly class DateRangeCondition implements PricingConditionInterface
{
    public function __construct(
        private DateTimeImmutable $from,
        private DateTimeImmutable $to,
    ) {
        if ($this->from > $this->to) {
            throw new \InvalidArgumentException('Date range start must be before or equal to end.');
        }
    }

    public function matches(PricingContext $context): bool
    {
        $activityDay = $context->availability()->dateTime()->setTime(0, 0);
        $from = $this->from->setTime(0, 0);
        $to = $this->to->setTime(0, 0);

        return $activityDay >= $from && $activityDay <= $to;
    }
}