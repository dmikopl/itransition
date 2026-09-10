<?php

declare(strict_types=1);

namespace App\Pricing\Application;

use App\Pricing\Domain\Model\AppliedAdjustment;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricedTicketCategory;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\PricingResult;
use App\Pricing\Domain\Model\PricingRule;

final class PricingEngine
{
    /**
     * @param list<PricingRule> $rules
     */
    public function calculate(PricingContext $context, array $rules): PricingResult
    {
        $applicableRules = array_values(array_filter(
            $rules,
            static fn (PricingRule $rule): bool => $rule->appliesTo($context),
        ));

        usort(
            $applicableRules,
            static function (PricingRule $left, PricingRule $right): int {
                $priorityComparison = $right->priority() <=> $left->priority();

                if (0 !== $priorityComparison) {
                    return $priorityComparison;
                }

                return $left->id() <=> $right->id();
            },
        );

        $pricedCategories = [];
        $originalTotal = Money::fromCents(0);
        $finalTotal = Money::fromCents(0);

        foreach ($context->availability()->ticketCategories() as $ticketCategory) {
            $originalPrice = $ticketCategory->price();
            $currentPrice = $originalPrice;
            $appliedAdjustments = [];

            foreach ($applicableRules as $rule) {
                $priceBefore = $currentPrice;
                $currentPrice = $rule->adjustment()->apply($currentPrice);
                $appliedAdjustments[] = new AppliedAdjustment(
                    ruleName: $rule->name(),
                    priceBefore: $priceBefore,
                    priceAfter: $currentPrice,
                );
            }

            $pricedCategories[] = new PricedTicketCategory(
                name: $ticketCategory->name(),
                originalPrice: $originalPrice,
                finalPrice: $currentPrice,
                appliedAdjustments: $appliedAdjustments,
            );

            $originalTotal = $originalTotal->add($originalPrice);
            $finalTotal = $finalTotal->add($currentPrice);
        }

        return new PricingResult(
            appliedRules: $applicableRules,
            categories: $pricedCategories,
            originalTotal: $originalTotal,
            finalTotal: $finalTotal,
        );
    }
}
