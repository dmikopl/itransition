<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Condition;

use App\Pricing\Domain\Model\PricingContext;

final readonly class DayOfWeekCondition implements PricingConditionInterface
{
    /**
     * @param list<int> $daysOfWeek ISO-8601 values: 1 (Monday) ... 7 (Sunday)
     */
    public function __construct(
        private array $daysOfWeek,
    ) {
        if ([] === $this->daysOfWeek) {
            throw new \InvalidArgumentException('At least one day of week is required.');
        }

        foreach ($this->daysOfWeek as $day) {
            if ($day < 1 || $day > 7) {
                throw new \InvalidArgumentException('Day of week must be between 1 and 7.');
            }
        }
    }

    public function matches(PricingContext $context): bool
    {
        $day = (int) $context->availability()->dateTime()->format('N');

        return \in_array($day, $this->daysOfWeek, true);
    }
}
