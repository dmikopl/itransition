<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

final readonly class PricingContext
{
    public function __construct(
        private Availability $availability,
        private \DateTimeImmutable $bookingDate,
    ) {
    }

    public function availability(): Availability
    {
        return $this->availability;
    }

    public function bookingDate(): \DateTimeImmutable
    {
        return $this->bookingDate;
    }

    public function daysInAdvance(): int
    {
        $activityDay = $this->availability->dateTime()->setTime(0, 0);
        $bookingDay = $this->bookingDate->setTime(0, 0);
        $interval = $bookingDay->diff($activityDay);

        if (1 === $interval->invert) {
            return 0;
        }

        return (int) $interval->days;
    }
}
