<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pricing;

use App\Pricing\Application\PricingEngine;
use App\Pricing\Domain\Adjustment\PercentageDiscount;
use App\Pricing\Domain\Condition\AdvanceBookingCondition;
use App\Pricing\Domain\Condition\DateRangeCondition;
use App\Pricing\Domain\Condition\DayOfWeekCondition;
use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\Availability;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\PricingRule;
use App\Pricing\Domain\Model\TicketCategory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PricingEngineTest extends TestCase
{
    private PricingEngine $engine;

    protected function setUp(): void
    {
        $this->engine = new PricingEngine();
    }

    public function testBriefExampleAppliesTenPercentMondayJanuaryAdvanceDiscount(): void
    {
        $rule = $this->mondayJanuaryAdvanceRule(id: 1, priority: 100);
        $context = $this->context(
            activityDateTime: '2026-01-12 09:00',
            bookingDate: '2026-01-01 10:00',
        );

        $result = $this->engine->calculate($context, [$rule]);

        self::assertCount(1, $result->appliedRules());
        self::assertSame($rule->name(), $result->appliedRules()[0]->name());

        self::assertCount(2, $result->categories());
        self::assertSame('Adult', $result->categories()[0]->name());
        self::assertTrue($result->categories()[0]->finalPrice()->equals(Money::fromDollars(90)));
        self::assertSame('Child', $result->categories()[1]->name());
        self::assertTrue($result->categories()[1]->finalPrice()->equals(Money::fromDollars(45)));

        self::assertTrue($result->originalTotal()->equals(Money::fromDollars(150)));
        self::assertTrue($result->finalTotal()->equals(Money::fromDollars(135)));

        $adultTrail = $result->categories()[0]->appliedAdjustments();
        self::assertCount(1, $adultTrail);
        self::assertSame($rule->name(), $adultTrail[0]->ruleName());
        self::assertTrue($adultTrail[0]->priceBefore()->equals(Money::fromDollars(100)));
        self::assertTrue($adultTrail[0]->priceAfter()->equals(Money::fromDollars(90)));
    }

    #[DataProvider('nonMatchingBriefCases')]
    public function testBriefRuleDoesNotApplyWhenAnyConditionFails(
        string $activityDateTime,
        string $bookingDate,
    ): void {
        $rule = $this->mondayJanuaryAdvanceRule(id: 1, priority: 100);
        $context = $this->context($activityDateTime, $bookingDate);

        $result = $this->engine->calculate($context, [$rule]);

        self::assertCount(0, $result->appliedRules());
        self::assertTrue($result->categories()[0]->finalPrice()->equals(Money::fromDollars(100)));
        self::assertTrue($result->categories()[1]->finalPrice()->equals(Money::fromDollars(50)));
        self::assertTrue($result->finalTotal()->equals(Money::fromDollars(150)));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function nonMatchingBriefCases(): iterable
    {
        yield 'tuesday in january' => ['2026-01-13 09:00', '2026-01-01 10:00'];
        yield 'monday in february' => ['2026-02-09 09:00', '2026-01-01 10:00'];
        yield 'monday january but only five days ahead' => ['2026-01-06 09:00', '2026-01-01 10:00'];
    }

    public function testRulesAreAppliedInDescendingPriorityOrderAndStack(): void
    {
        $lowPriority = new PricingRule(
            id: 1,
            name: '10% discount',
            priority: 10,
            conditions: [],
            adjustment: new PercentageDiscount(10),
        );
        $highPriority = new PricingRule(
            id: 2,
            name: '20% discount',
            priority: 100,
            conditions: [],
            adjustment: new PercentageDiscount(20),
        );

        $result = $this->engine->calculate(
            $this->context('2026-01-12 09:00', '2026-01-01 10:00'),
            [$lowPriority, $highPriority],
        );

        self::assertSame(
            ['20% discount', '10% discount'],
            array_map(static fn (PricingRule $rule): string => $rule->name(), $result->appliedRules()),
        );

        $adult = $result->categories()[0];
        self::assertTrue($adult->finalPrice()->equals(Money::fromDollars(72)));
        self::assertTrue($adult->appliedAdjustments()[0]->priceAfter()->equals(Money::fromDollars(80)));
        self::assertTrue($adult->appliedAdjustments()[1]->priceAfter()->equals(Money::fromDollars(72)));
    }

    public function testEqualPriorityUsesLowerIdFirst(): void
    {
        $second = new PricingRule(
            id: 20,
            name: 'second by id',
            priority: 50,
            conditions: [],
            adjustment: new PercentageDiscount(10),
        );
        $first = new PricingRule(
            id: 10,
            name: 'first by id',
            priority: 50,
            conditions: [],
            adjustment: new PercentageDiscount(10),
        );

        $result = $this->engine->calculate(
            $this->context('2026-01-12 09:00', '2026-01-01 10:00'),
            [$second, $first],
        );

        self::assertSame(
            ['first by id', 'second by id'],
            array_map(static fn (PricingRule $rule): string => $rule->name(), $result->appliedRules()),
        );
    }

    private function mondayJanuaryAdvanceRule(int $id, int $priority): PricingRule
    {
        return new PricingRule(
            id: $id,
            name: '10% Monday January advance-booking discount',
            priority: $priority,
            conditions: [
                new DayOfWeekCondition([1]),
                new DateRangeCondition(
                    new \DateTimeImmutable('2026-01-01'),
                    new \DateTimeImmutable('2026-01-31'),
                ),
                new AdvanceBookingCondition(7),
            ],
            adjustment: new PercentageDiscount(10),
        );
    }

    private function context(string $activityDateTime, string $bookingDate): PricingContext
    {
        return new PricingContext(
            availability: new Availability(
                activity: Activity::CityTour,
                option: ActivityOption::Standard,
                dateTime: new \DateTimeImmutable($activityDateTime),
                ticketCategories: [
                    new TicketCategory('Adult', Money::fromDollars(100)),
                    new TicketCategory('Child', Money::fromDollars(50)),
                ],
            ),
            bookingDate: new \DateTimeImmutable($bookingDate),
        );
    }
}
