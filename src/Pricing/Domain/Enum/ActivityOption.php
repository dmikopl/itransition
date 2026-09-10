<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Enum;

enum ActivityOption: string
{
    case Standard = 'standard';
    case Guided = 'guided';
    case Private = 'private';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'Standard',
            self::Guided => 'Guided',
            self::Private => 'Private',
        };
    }
}