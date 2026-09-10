<?php

declare(strict_types=1);

namespace App\Pricing\Infrastructure\Doctrine;

use App\Pricing\Domain\Adjustment\FixedDiscount;
use App\Pricing\Domain\Adjustment\PercentageDiscount;
use App\Pricing\Domain\Adjustment\PercentageSurcharge;
use App\Pricing\Domain\Adjustment\PriceAdjustmentInterface;
use App\Pricing\Domain\Condition\ActivityCondition;
use App\Pricing\Domain\Condition\AdvanceBookingCondition;
use App\Pricing\Domain\Condition\DateRangeCondition;
use App\Pricing\Domain\Condition\DayOfWeekCondition;
use App\Pricing\Domain\Condition\OptionCondition;
use App\Pricing\Domain\Condition\PricingConditionInterface;
use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingRule;

final class PricingRuleFactory
{
    public function fromEntity(PricingRuleEntity $entity): PricingRule
    {
        $id = $entity->getId();
        if ($id === null) {
            throw new \LogicException('Cannot map a pricing rule entity without an id.');
        }

        return new PricingRule(
            id: $id,
            name: $entity->getName(),
            priority: $entity->getPriority(),
            conditions: $this->buildConditions($entity),
            adjustment: $this->buildAdjustment($entity),
        );
    }

    /**
     * @return list<PricingConditionInterface>
     */
    private function buildConditions(PricingRuleEntity $entity): array
    {
        $conditions = [];

        if ($entity->getActivity() !== null) {
            $conditions[] = new ActivityCondition(Activity::from($entity->getActivity()));
        }

        if ($entity->getActivityOption() !== null) {
            $conditions[] = new OptionCondition(ActivityOption::from($entity->getActivityOption()));
        }

        if ($entity->getDateFrom() !== null && $entity->getDateTo() !== null) {
            $conditions[] = new DateRangeCondition($entity->getDateFrom(), $entity->getDateTo());
        }

        if ($entity->getDaysOfWeek() !== null) {
            $conditions[] = new DayOfWeekCondition($entity->getDaysOfWeek());
        }

        if ($entity->getMinAdvanceDays() !== null) {
            $conditions[] = new AdvanceBookingCondition($entity->getMinAdvanceDays());
        }

        return $conditions;
    }

    private function buildAdjustment(PricingRuleEntity $entity): PriceAdjustmentInterface
    {
        return match ($entity->getAdjustmentType()) {
            'percentage_discount' => new PercentageDiscount($entity->getAdjustmentValue()),
            'percentage_surcharge' => new PercentageSurcharge($entity->getAdjustmentValue()),
            'fixed_discount' => new FixedDiscount(Money::fromCents($entity->getAdjustmentValue())),
            default => throw new \InvalidArgumentException(sprintf(
                'Unknown adjustment type "%s".',
                $entity->getAdjustmentType(),
            )),
        };
    }
}