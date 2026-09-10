<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

use App\Pricing\Domain\Adjustment\PriceAdjustmentInterface;
use App\Pricing\Domain\Condition\PricingConditionInterface;

final readonly class PricingRule
{
    /**
     * @param list<PricingConditionInterface> $conditions
     */
    public function __construct(
        private int $id,
        private string $name,
        private int $priority,
        private array $conditions,
        private PriceAdjustmentInterface $adjustment,
    ) {
        if (trim($this->name) === '') {
            throw new \InvalidArgumentException('Pricing rule name cannot be empty.');
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function priority(): int
    {
        return $this->priority;
    }

    /**
     * @return list<PricingConditionInterface>
     */
    public function conditions(): array
    {
        return $this->conditions;
    }

    public function adjustment(): PriceAdjustmentInterface
    {
        return $this->adjustment;
    }

    public function appliesTo(PricingContext $context): bool
    {
        foreach ($this->conditions as $condition) {
            if (!$condition->matches($context)) {
                return false;
            }
        }

        return true;
    }
}