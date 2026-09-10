<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

final readonly class PricingResult
{
    /**
     * @param list<PricingRule> $appliedRules
     * @param list<PricedTicketCategory> $categories
     */
    public function __construct(
        private array $appliedRules,
        private array $categories,
        private Money $originalTotal,
        private Money $finalTotal,
    ) {
    }

    /**
     * @return list<PricingRule>
     */
    public function appliedRules(): array
    {
        return $this->appliedRules;
    }

    /**
     * @return list<PricedTicketCategory>
     */
    public function categories(): array
    {
        return $this->categories;
    }

    public function originalTotal(): Money
    {
        return $this->originalTotal;
    }

    public function finalTotal(): Money
    {
        return $this->finalTotal;
    }
}