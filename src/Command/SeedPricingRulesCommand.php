<?php

declare(strict_types=1);

namespace App\Command;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Infrastructure\Doctrine\PricingRuleEntity;
use App\Pricing\Infrastructure\Doctrine\PricingRuleRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:pricing:seed',
    description: 'Seed demo pricing rules for the calculator',
)]
final class SeedPricingRulesCommand extends Command
{
    public function __construct(
        private readonly PricingRuleRepository $ruleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $existingNames = [];
        foreach ($this->ruleRepository->findAllOrdered() as $existing) {
            $existingNames[$existing->name()] = $existing->id();
        }

        $seeded = 0;
        foreach ($this->rules() as $rule) {
            $name = $rule->getName();
            if (isset($existingNames[$name])) {
                $io->writeln(sprintf('Skip "%s" (id=%d).', $name, $existingNames[$name]));
                continue;
            }

            $this->ruleRepository->save($rule);
            $io->writeln(sprintf('Seeded "%s".', $name));
            ++$seeded;
        }

        $io->success(sprintf('Done. New rules: %d.', $seeded));

        return Command::SUCCESS;
    }

    /**
     * @return list<PricingRuleEntity>
     */
    private function rules(): array
    {
        return [
            new PricingRuleEntity(
                name: '10% Monday January advance-booking discount',
                priority: 100,
                adjustmentType: 'percentage_discount',
                adjustmentValue: 10,
                activity: Activity::CityTour->value,
                activityOption: ActivityOption::Standard->value,
                dateFrom: new DateTimeImmutable('2026-01-01'),
                dateTo: new DateTimeImmutable('2026-01-31'),
                daysOfWeek: [1],
                minAdvanceDays: 7,
            ),
            new PricingRuleEntity(
                name: '15% museum weekday discount',
                priority: 100,
                adjustmentType: 'percentage_discount',
                adjustmentValue: 15,
                activity: Activity::MuseumVisit->value,
                activityOption: null,
                dateFrom: null,
                dateTo: null,
                daysOfWeek: [1, 2, 3, 4, 5],
                minAdvanceDays: null,
            ),
            new PricingRuleEntity(
                name: '$3 museum loyalty fixed discount',
                priority: 50,
                adjustmentType: 'fixed_discount',
                adjustmentValue: 300,
                activity: Activity::MuseumVisit->value,
                activityOption: null,
                dateFrom: null,
                dateTo: null,
                daysOfWeek: null,
                minAdvanceDays: null,
            ),
            new PricingRuleEntity(
                name: '20% city tour weekend surcharge',
                priority: 80,
                adjustmentType: 'percentage_surcharge',
                adjustmentValue: 20,
                activity: Activity::CityTour->value,
                activityOption: ActivityOption::Standard->value,
                dateFrom: null,
                dateTo: null,
                daysOfWeek: [6, 7],
                minAdvanceDays: null,
            ),
        ];
    }
}
