<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;

final class CalculatorInput
{
    /** @param list<TicketCategoryInput> $ticketCategories */
    public function __construct(
        public ?Activity $activity = null,
        public ?ActivityOption $option = null,
        public ?\DateTimeImmutable $activityDate = null,
        public ?\DateTimeImmutable $bookingDate = null,
        public array $ticketCategories = [],
    ) {
        if ([] === $this->ticketCategories) {
            $this->ticketCategories = [
                new TicketCategoryInput('Adult', 100),
                new TicketCategoryInput('Child', 50),
            ];
        }
    }
}
