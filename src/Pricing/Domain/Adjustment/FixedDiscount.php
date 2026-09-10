<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Adjustment;

use App\Pricing\Domain\Model\Money;

final readonly class FixedDiscount implements PriceAdjustmentInterface
{
    public function __construct(
        private Money $amount,
    ) {
        if ($this->amount->amountInCents() <= 0) {
            throw new \InvalidArgumentException('Fixed discount must be greater than zero.');
        }
    }

    public function apply(Money $price): Money
    {
        if ($this->amount->amountInCents() > $price->amountInCents()) {
            return Money::fromCents(0);
        }

        return $price->subtract($this->amount);
    }

    public function describe(): string
    {
        return sprintf('%s discount', $this->amount->format());
    }
}
