<?php

declare(strict_types=1);

namespace App\Form\Dto;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;

final class PricingRuleInput
{
    /** @param list<int> $daysOfWeek */
    public function __construct(
        public string $name = '',
        public int $priority = 100,
        public string $adjustmentType = 'percentage_discount',
        public ?int $adjustmentValue = null,
        public ?Activity $activity = null,
        public ?ActivityOption $option = null,
        public ?\DateTimeImmutable $dateFrom = null,
        public ?\DateTimeImmutable $dateTo = null,
        public array $daysOfWeek = [],
        public ?int $minAdvanceDays = null,
    ) {
    }
}
