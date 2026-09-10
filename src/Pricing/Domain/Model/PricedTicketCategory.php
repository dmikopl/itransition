<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

final readonly class PricedTicketCategory
{
    /**
     * @param list<AppliedAdjustment> $appliedAdjustments
     */
    public function __construct(
        private string $name,
        private Money $originalPrice,
        private Money $finalPrice,
        private array $appliedAdjustments,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function originalPrice(): Money
    {
        return $this->originalPrice;
    }

    public function finalPrice(): Money
    {
        return $this->finalPrice;
    }

    /**
     * @return list<AppliedAdjustment>
     */
    public function appliedAdjustments(): array
    {
        return $this->appliedAdjustments;
    }
}
