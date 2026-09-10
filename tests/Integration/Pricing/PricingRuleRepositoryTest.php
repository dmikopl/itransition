<?php

declare(strict_types=1);

namespace App\Tests\Integration\Pricing;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Domain\Model\Availability;
use App\Pricing\Domain\Model\Money;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\TicketCategory;
use App\Pricing\Infrastructure\Doctrine\PricingRuleEntity;
use App\Pricing\Infrastructure\Doctrine\PricingRuleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PricingRuleRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private PricingRuleRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->repository = static::getContainer()->get(PricingRuleRepository::class);

        $this->entityManager->createQuery('DELETE FROM App\Pricing\Infrastructure\Doctrine\PricingRuleEntity e')->execute();
    }

    public function testSaveAndFindAllOrderedMapsToDomainRule(): void
    {
        $entity = new PricingRuleEntity(
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
        );

        $this->repository->save($entity);

        $rules = $this->repository->findAllOrdered();

        self::assertCount(1, $rules);
        self::assertSame('10% Monday January advance-booking discount', $rules[0]->name());
        self::assertSame(100, $rules[0]->priority());
        self::assertCount(5, $rules[0]->conditions());
        self::assertTrue(
            $rules[0]->appliesTo($this->briefContext()),
        );
    }

    public function testFindCandidatesMatchesDomainFiltering(): void
    {
        $this->repository->save(new PricingRuleEntity(
            name: 'matching january monday',
            priority: 50,
            adjustmentType: 'percentage_discount',
            adjustmentValue: 10,
            dateFrom: new DateTimeImmutable('2026-01-01'),
            dateTo: new DateTimeImmutable('2026-01-31'),
            daysOfWeek: [1],
            minAdvanceDays: 7,
        ));

        $this->repository->save(new PricingRuleEntity(
            name: 'wrong activity',
            priority: 40,
            adjustmentType: 'percentage_discount',
            adjustmentValue: 5,
            activity: Activity::MuseumVisit->value,
        ));

        $this->repository->save(new PricingRuleEntity(
            name: 'february only',
            priority: 30,
            adjustmentType: 'percentage_discount',
            adjustmentValue: 15,
            dateFrom: new DateTimeImmutable('2026-02-01'),
            dateTo: new DateTimeImmutable('2026-02-28'),
        ));

        $this->repository->save(new PricingRuleEntity(
            name: 'needs more advance days',
            priority: 20,
            adjustmentType: 'percentage_discount',
            adjustmentValue: 20,
            minAdvanceDays: 60,
        ));

        $context = $this->briefContext();

        $candidates = $this->repository->findCandidates($context);
        $domainFiltered = array_values(array_filter(
            $this->repository->findAllOrdered(),
            static fn ($rule): bool => $rule->appliesTo($context),
        ));

        $candidateNames = array_map(static fn ($rule): string => $rule->name(), $candidates);
        $domainNames = array_map(static fn ($rule): string => $rule->name(), $domainFiltered);

        self::assertSame(['matching january monday'], $domainNames);
        self::assertContains('matching january monday', $candidateNames);
        self::assertNotContains('wrong activity', $candidateNames);
        self::assertNotContains('february only', $candidateNames);
        self::assertNotContains('needs more advance days', $candidateNames);

        foreach ($domainFiltered as $rule) {
            self::assertContains($rule->name(), $candidateNames);
        }
    }

    private function briefContext(): PricingContext
    {
        return new PricingContext(
            availability: new Availability(
                activity: Activity::CityTour,
                option: ActivityOption::Standard,
                dateTime: new DateTimeImmutable('2026-01-12 09:00'),
                ticketCategories: [
                    new TicketCategory('Adult', Money::fromDollars(100)),
                    new TicketCategory('Child', Money::fromDollars(50)),
                ],
            ),
            bookingDate: new DateTimeImmutable('2026-01-01 10:00'),
        );
    }
}
