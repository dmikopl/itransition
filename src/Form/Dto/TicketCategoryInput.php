<?php

declare(strict_types=1);

namespace App\Form\Dto;

final class TicketCategoryInput
{
    public function __construct(
        public string $name = '',
        public ?float $price = null,
    ) {
    }
}