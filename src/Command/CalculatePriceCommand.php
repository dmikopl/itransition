<?php

declare(strict_types=1);

namespace App\Command;

use App\Pricing\Application\PriceCalculator;
use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\Availability;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\TicketCategory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:pricing:calculate',
    description: 'Calculate ticket prices using the pricing engine',
)]
final class CalculatePriceCommand extends Command
{
    public function __construct(
        private readonly PriceCalculator $priceCalculator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('activity', null, InputOption::VALUE_REQUIRED, 'Activity value', Activity::CityTour->value)
            ->addOption('option', null, InputOption::VALUE_REQUIRED, 'Activity option value', ActivityOption::Standard->value)
            ->addOption('activity-date', null, InputOption::VALUE_REQUIRED, 'Activity date (Y-m-d)', '2026-01-12')
            ->addOption('booking-date', null, InputOption::VALUE_REQUIRED, 'Booking date (Y-m-d)', '2026-01-01')
            ->addOption(
                'ticket',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Ticket as Name:dollars (repeatable)',
                ['Adult:100', 'Child:50'],
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $activity = Activity::from((string) $input->getOption('activity'));
        $option = ActivityOption::from((string) $input->getOption('option'));
        $activityDate = new \DateTimeImmutable((string) $input->getOption('activity-date'));
        $bookingDate = new \DateTimeImmutable((string) $input->getOption('booking-date'));

        /** @var list<string> $ticketInputs */
        $ticketInputs = $input->getOption('ticket');
        $ticketCategories = [];
        foreach ($ticketInputs as $ticketInput) {
            [$name, $dollars] = explode(':', $ticketInput, 2);
            $ticketCategories[] = new TicketCategory(
                name: $name,
                price: Money::fromDollars((int) $dollars),
            );
        }

        $result = $this->priceCalculator->calculate(new PricingContext(
            availability: new Availability(
                activity: $activity,
                option: $option,
                dateTime: $activityDate,
                ticketCategories: $ticketCategories,
            ),
            bookingDate: $bookingDate,
        ));

        $io->section('Applied rules');
        if ([] === $result->appliedRules()) {
            $io->writeln('None');
        } else {
            foreach ($result->appliedRules() as $rule) {
                $io->writeln(sprintf('- %s (priority %d)', $rule->name(), $rule->priority()));
            }
        }

        $io->section('Categories');
        foreach ($result->categories() as $category) {
            $io->writeln(sprintf(
                '%s: %s → %s',
                $category->name(),
                $category->originalPrice()->format(),
                $category->finalPrice()->format(),
            ));
        }

        $io->success(sprintf(
            'Total: %s → %s',
            $result->originalTotal()->format(),
            $result->finalTotal()->format(),
        ));

        return Command::SUCCESS;
    }
}
