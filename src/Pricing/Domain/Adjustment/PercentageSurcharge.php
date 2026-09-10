<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Adjustment;

use App\Pricing\Domain\Model\Money;

final readonly class PercentageSurcharge implements PriceAdjustmentInterface
{
    public function __construct(
        private int $percentage,
    ) {
        if ($this->percentage <= 0) {
            throw new \InvalidArgumentException('Percentage surcharge must be greater than zero.');
        }
    }

    public function apply(Money $price): Money
    {
        return $price->multiply(100 + $this->percentage)->divide(100);
    }

    public function describe(): string
    {
        return sprintf('%d%% surcharge', $this->percentage);
    }
}