<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Adjustment;

use App\Pricing\Domain\Model\Money;

final readonly class PercentageDiscount implements PriceAdjustmentInterface
{
    public function __construct(
        private int $percentage,
    ) {
        if ($this->percentage <= 0 || $this->percentage > 100) {
            throw new \InvalidArgumentException('Percentage discount must be between 1 and 100.');
        }
    }

    public function apply(Money $price): Money
    {
        return $price->multiply(100 - $this->percentage)->divide(100);
    }

    public function describe(): string
    {
        return sprintf('%d%% discount', $this->percentage);
    }
}
