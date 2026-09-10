<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

final readonly class TicketCategory
{
    public function __construct(
        private string $name,
        private Money $price,
    ) {
        if ('' === trim($this->name)) {
            throw new \InvalidArgumentException('Ticket category name cannot be empty.');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }
}
