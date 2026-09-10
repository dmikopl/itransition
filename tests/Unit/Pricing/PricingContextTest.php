<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pricing;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\Availability;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\TicketCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PricingContextTest extends TestCase
{
    #[DataProvider('daysInAdvanceCases')]
    public function testDaysInAdvanceIgnoresClockTime(
        string $activityDateTime,
        string $bookingDate,
        int $expectedDays,
    ): void {
        $context = new PricingContext(
            availability: $this->availability(new \DateTimeImmutable($activityDateTime)),
            bookingDate: new \DateTimeImmutable($bookingDate),
        );

        self::assertSame($expectedDays, $context->daysInAdvance());
    }

    /**
     * @return iterable<string, array{string, string, int}>
     */
    public static function daysInAdvanceCases(): iterable
    {
        yield 'brief example january' => ['2026-01-12 09:00', '2026-01-01 23:59', 11];
        yield 'exactly seven days' => ['2026-01-08 18:30', '2026-01-01 00:00', 7];
        yield 'same calendar day' => ['2026-01-01 20:00', '2026-01-01 08:00', 0];
        yield 'booking after activity counts as zero' => ['2026-09-12 09:00', '2026-09-13 09:00', 0];
    }

    public function testAvailabilityRejectsEmptyTicketCategories(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Availability(
            activity: Activity::CityTour,
            option: ActivityOption::Standard,
            dateTime: new \DateTimeImmutable('2026-01-12 09:00'),
            ticketCategories: [],
        );
    }

    public function testTicketCategoryRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new TicketCategory(name: '   ', price: Money::fromDollars(100));
    }

    public function testEnumLabelsAreHumanReadable(): void
    {
        self::assertSame('city_tour', Activity::CityTour->value);
        self::assertSame('City tour', Activity::CityTour->label());
        self::assertSame('guided', ActivityOption::Guided->value);
        self::assertSame('Guided', ActivityOption::Guided->label());
    }

    private function availability(\DateTimeImmutable $dateTime): Availability
    {
        return new Availability(
            activity: Activity::CityTour,
            option: ActivityOption::Standard,
            dateTime: $dateTime,
            ticketCategories: [
                new TicketCategory('Adult', Money::fromDollars(100)),
                new TicketCategory('Child', Money::fromDollars(50)),
            ],
        );
    }
}
