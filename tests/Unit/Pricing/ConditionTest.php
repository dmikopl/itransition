<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pricing;

use App\Pricing\Domain\Condition\ActivityCondition;
use App\Pricing\Domain\Condition\AdvanceBookingCondition;
use App\Pricing\Domain\Condition\DateRangeCondition;
use App\Pricing\Domain\Condition\DayOfWeekCondition;
use App\Pricing\Domain\Condition\OptionCondition;
use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\Availability;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\TicketCategory;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConditionTest extends TestCase
{
    public function testActivityConditionMatchesOnlyExpectedActivity(): void
    {
        $condition = new ActivityCondition(Activity::CityTour);
        $matching = $this->context(activity: Activity::CityTour);
        $other = $this->context(activity: Activity::MuseumVisit);

        self::assertTrue($condition->matches($matching));
        self::assertFalse($condition->matches($other));
    }

    public function testOptionConditionMatchesOnlyExpectedOption(): void
    {
        $condition = new OptionCondition(ActivityOption::Guided);
        $matching = $this->context(option: ActivityOption::Guided);
        $other = $this->context(option: ActivityOption::Standard);

        self::assertTrue($condition->matches($matching));
        self::assertFalse($condition->matches($other));
    }

    #[DataProvider('dateRangeCases')]
    public function testDateRangeCondition(string $activityDate, bool $expected): void
    {
        $condition = new DateRangeCondition(
            from: new DateTimeImmutable('2026-01-01'),
            to: new DateTimeImmutable('2026-01-31'),
        );

        $context = $this->context(activityDateTime: $activityDate);

        self::assertSame($expected, $condition->matches($context));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function dateRangeCases(): iterable
    {
        yield 'inside january' => ['2026-01-12 09:00', true];
        yield 'start boundary' => ['2026-01-01 23:59', true];
        yield 'end boundary' => ['2026-01-31 00:00', true];
        yield 'before range' => ['2025-12-31 09:00', false];
        yield 'after range' => ['2026-02-01 09:00', false];
    }

    #[DataProvider('dayOfWeekCases')]
    public function testDayOfWeekCondition(string $activityDate, bool $expected): void
    {
        $condition = new DayOfWeekCondition([1]);

        $context = $this->context(activityDateTime: $activityDate);

        self::assertSame($expected, $condition->matches($context));
    }

    /**
     * @return iterable<string, array{string, bool}>
     */
    public static function dayOfWeekCases(): iterable
    {
        yield 'monday' => ['2026-01-12 09:00', true];
        yield 'tuesday' => ['2026-01-13 09:00', false];
    }

    #[DataProvider('advanceBookingCases')]
    public function testAdvanceBookingCondition(string $activityDate, string $bookingDate, bool $expected): void
    {
        $condition = new AdvanceBookingCondition(7);
        $context = $this->context(
            activityDateTime: $activityDate,
            bookingDate: $bookingDate,
        );

        self::assertSame($expected, $condition->matches($context));
    }

    /**
     * @return iterable<string, array{string, string, bool}>
     */
    public static function advanceBookingCases(): iterable
    {
        yield 'six days' => ['2026-01-07 09:00', '2026-01-01 09:00', false];
        yield 'seven days' => ['2026-01-08 09:00', '2026-01-01 09:00', true];
        yield 'eight days' => ['2026-01-09 09:00', '2026-01-01 09:00', true];
        yield 'brief example eleven days' => ['2026-01-12 09:00', '2026-01-01 09:00', true];
    }

    public function testCombinedBriefRuleConditionsMatch(): void
    {
        $context = $this->context(
            activityDateTime: '2026-01-12 09:00',
            bookingDate: '2026-01-01 10:00',
        );

        self::assertTrue((new DayOfWeekCondition([1]))->matches($context));
        self::assertTrue((new DateRangeCondition(
            new DateTimeImmutable('2026-01-01'),
            new DateTimeImmutable('2026-01-31'),
        ))->matches($context));
        self::assertTrue((new AdvanceBookingCondition(7))->matches($context));
    }

    private function context(
        Activity $activity = Activity::CityTour,
        ActivityOption $option = ActivityOption::Standard,
        string $activityDateTime = '2026-01-12 09:00',
        string $bookingDate = '2026-01-01 10:00',
    ): PricingContext {
        return new PricingContext(
            availability: new Availability(
                activity: $activity,
                option: $option,
                dateTime: new DateTimeImmutable($activityDateTime),
                ticketCategories: [
                    new TicketCategory('Adult', Money::fromDollars(100)),
                ],
            ),
            bookingDate: new DateTimeImmutable($bookingDate),
        );
    }
}
