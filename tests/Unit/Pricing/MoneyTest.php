<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pricing;

use App\Pricing\Domain\Model\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testFromDollarsConvertsToCents(): void
    {
        $money = Money::fromDollars(100);

        self::assertSame(10000, $money->amountInCents());
        self::assertSame('USD', $money->currency());
        self::assertSame('$100.00', $money->format());
    }

    public function testFromCentsKeepsExactAmount(): void
    {
        $money = Money::fromCents(5050);

        self::assertSame(5050, $money->amountInCents());
        self::assertSame('$50.50', $money->format());
    }

    public function testAddAndSubtract(): void
    {
        $left = Money::fromDollars(100);
        $right = Money::fromDollars(40);

        self::assertTrue($left->add($right)->equals(Money::fromDollars(140)));
        self::assertTrue($left->subtract($right)->equals(Money::fromDollars(60)));
    }

    public function testPercentageDiscountViaMultiplyAndDivide(): void
    {
        $price = Money::fromDollars(100);

        $discounted = $price->multiply(90)->divide(100);

        self::assertTrue($discounted->equals(Money::fromDollars(90)));
        self::assertSame('$90.00', $discounted->format());
    }

    public function testChildPriceDiscountExampleFromBrief(): void
    {
        $price = Money::fromDollars(50);

        $discounted = $price->multiply(90)->divide(100);

        self::assertTrue($discounted->equals(Money::fromDollars(45)));
    }

    #[DataProvider('halfUpDivisionCases')]
    public function testDivideUsesHalfUpRounding(int $cents, int $divisor, int $expectedCents): void
    {
        $result = Money::fromCents($cents)->divide($divisor);

        self::assertSame($expectedCents, $result->amountInCents());
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function halfUpDivisionCases(): iterable
    {
        yield 'exact division' => [1000, 10, 100];
        yield 'round half up' => [5, 2, 3];
        yield 'round down below half' => [4, 2, 2];
    }

    public function testNegativeAmountIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromCents(-1);
    }

    public function testDivideByZeroIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromDollars(10)->divide(0);
    }

    public function testSubtractThatWouldGoNegativeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Money::fromDollars(10)->subtract(Money::fromDollars(20));
    }
}
