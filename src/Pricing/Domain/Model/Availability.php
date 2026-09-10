<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use DateTimeImmutable;

final readonly class Availability
{
    /**
     * @param list<TicketCategory> $ticketCategories
     */
    public function __construct(
        private Activity $activity,
        private ActivityOption $option,
        private DateTimeImmutable $dateTime,
        private array $ticketCategories,
    ) {
        if ($this->ticketCategories === []) {
            throw new \InvalidArgumentException('Availability must contain at least one ticket category.');
        }
    }

    public function activity(): Activity
    {
        return $this->activity;
    }

    public function option(): ActivityOption
    {
        return $this->option;
    }

    public function dateTime(): DateTimeImmutable
    {
        return $this->dateTime;
    }

    /**
     * @return list<TicketCategory>
     */
    public function ticketCategories(): array
    {
        return $this->ticketCategories;
    }
}