<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pricing;

use App\Pricing\Domain\Adjustment\FixedDiscount;
use App\Pricing\Domain\Adjustment\PercentageDiscount;
use App\Pricing\Domain\Adjustment\PercentageSurcharge;
use App\Pricing\Domain\Model\Money;
use PHPUnit\Framework\TestCase;

final class AdjustmentTest extends TestCase
{
    public function testPercentageDiscountMatchesBriefExample(): void
    {
        $discount = new PercentageDiscount(10);

        self::assertTrue(
            $discount->apply(Money::fromDollars(100))->equals(Money::fromDollars(90)),
        );
        self::assertTrue(
            $discount->apply(Money::fromDollars(50))->equals(Money::fromDollars(45)),
        );
        self::assertSame('10% discount', $discount->describe());
    }

    public function testFixedDiscountSubtractsCents(): void
    {
        $discount = new FixedDiscount(Money::fromDollars(15));

        self::assertTrue(
            $discount->apply(Money::fromDollars(100))->equals(Money::fromDollars(85)),
        );
        self::assertSame('$15.00 discount', $discount->describe());
    }

    public function testFixedDiscountDoesNotGoBelowZero(): void
    {
        $discount = new FixedDiscount(Money::fromDollars(30));

        self::assertTrue(
            $discount->apply(Money::fromDollars(20))->equals(Money::fromCents(0)),
        );
    }

    public function testPercentageSurchargeIncreasesPrice(): void
    {
        $surcharge = new PercentageSurcharge(10);

        self::assertTrue(
            $surcharge->apply(Money::fromDollars(100))->equals(Money::fromDollars(110)),
        );
        self::assertSame('10% surcharge', $surcharge->describe());
    }

    public function testInvalidPercentageDiscountIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PercentageDiscount(0);
    }
}
