<?php

declare(strict_types=1);

namespace App\Tests\Integration\Pricing;

use App\Pricing\Infrastructure\Doctrine\PricingRuleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class PricingCommandsTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Application $application;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->entityManager->createQuery('DELETE FROM App\Pricing\Infrastructure\Doctrine\PricingRuleEntity e')->execute();

        $this->application = new Application(self::$kernel);
    }

    public function testSeedIsIdempotentAndCalculateCoversDemoScenarios(): void
    {
        $seedTester = new CommandTester($this->application->find('app:pricing:seed'));
        self::assertSame(0, $seedTester->execute([]));
        self::assertStringContainsString('New rules: 4', $seedTester->getDisplay());

        self::assertSame(0, $seedTester->execute([]));
        self::assertStringContainsString('New rules: 0', $seedTester->getDisplay());
        self::assertStringContainsString('Skip', $seedTester->getDisplay());

        /** @var PricingRuleRepository $repository */
        $repository = static::getContainer()->get(PricingRuleRepository::class);
        self::assertCount(4, $repository->findAllOrdered());

        $calculateTester = new CommandTester($this->application->find('app:pricing:calculate'));

        self::assertSame(0, $calculateTester->execute([]));
        $briefDisplay = $calculateTester->getDisplay();
        self::assertStringContainsString('10% Monday January advance-booking discount', $briefDisplay);
        self::assertStringContainsString('Adult: $100.00 → $90.00', $briefDisplay);
        self::assertStringContainsString('Child: $50.00 → $45.00', $briefDisplay);
        self::assertStringContainsString('Total: $150.00 → $135.00', $briefDisplay);

        self::assertSame(0, $calculateTester->execute([
            '--activity' => 'museum_visit',
            '--option' => 'standard',
            '--activity-date' => '2026-01-12',
            '--booking-date' => '2026-01-01',
            '--ticket' => ['Adult:100', 'Child:50'],
        ]));
        $museumDisplay = $calculateTester->getDisplay();
        self::assertStringContainsString('15% museum weekday discount', $museumDisplay);
        self::assertStringContainsString('$3 museum loyalty fixed discount', $museumDisplay);
        self::assertStringContainsString('Adult: $100.00 → $82.00', $museumDisplay);
        self::assertStringContainsString('Child: $50.00 → $39.50', $museumDisplay);

        self::assertSame(0, $calculateTester->execute([
            '--activity' => 'city_tour',
            '--option' => 'standard',
            '--activity-date' => '2026-01-10',
            '--booking-date' => '2026-01-01',
            '--ticket' => ['Adult:100'],
        ]));
        $weekendDisplay = $calculateTester->getDisplay();
        self::assertStringContainsString('20% city tour weekend surcharge', $weekendDisplay);
        self::assertStringContainsString('Adult: $100.00 → $120.00', $weekendDisplay);
        self::assertStringContainsString('Total: $100.00 → $120.00', $weekendDisplay);
    }
}
