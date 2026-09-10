<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Model;

final readonly class Money
{
    public function __construct(
        private int $amountInCents,
        private string $currency = 'USD',
    ) {
        if ($this->amountInCents < 0) {
            throw new \InvalidArgumentException('Money amount cannot be negative.');
        }

        if ('USD' !== $this->currency) {
            throw new \InvalidArgumentException('Only USD is supported.');
        }
    }

    public static function fromCents(int $amountInCents): self
    {
        return new self($amountInCents);
    }

    public static function fromDollars(int $dollars): self
    {
        return new self($dollars * 100);
    }

    public function amountInCents(): int
    {
        return $this->amountInCents;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountInCents + $other->amountInCents, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);

        return new self($this->amountInCents - $other->amountInCents, $this->currency);
    }

    public function multiply(int $multiplier): self
    {
        return new self($this->amountInCents * $multiplier, $this->currency);
    }

    public function divide(int $divisor): self
    {
        if (0 === $divisor) {
            throw new \InvalidArgumentException('Division by zero.');
        }

        $amount = (int) round($this->amountInCents / $divisor, 0, \PHP_ROUND_HALF_UP);

        return new self($amount, $this->currency);
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }

    public function format(): string
    {
        return sprintf('$%s', number_format($this->amountInCents / 100, 2, '.', ','));
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException('Currency mismatch.');
        }
    }
}
