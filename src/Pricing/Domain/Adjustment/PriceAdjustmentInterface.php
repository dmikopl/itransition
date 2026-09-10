<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Adjustment;

use App\Pricing\Domain\Model\Money;

interface PriceAdjustmentInterface
{
    public function apply(Money $price): Money;

    public function describe(): string;
}