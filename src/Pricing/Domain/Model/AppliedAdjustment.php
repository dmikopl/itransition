<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

final readonly class AppliedAdjustment
{
    public function __construct(
        private string $ruleName,
        private Money $priceBefore,
        private Money $priceAfter,
    ) {
    }

    public function ruleName(): string
    {
        return $this->ruleName;
    }

    public function priceBefore(): Money
    {
        return $this->priceBefore;
    }

    public function priceAfter(): Money
    {
        return $this->priceAfter;
    }
}
