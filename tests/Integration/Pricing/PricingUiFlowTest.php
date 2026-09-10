<?php

declare(strict_types=1);

namespace App\Tests\Integration\Pricing;

use App\Pricing\Domain\Enum\Activity;
use App\Pricing\Domain\Enum\ActivityOption;
use App\Pricing\Infrastructure\Doctrine\PricingRuleEntity;
use App\Pricing\Infrastructure\Doctrine\PricingRuleRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PricingUiFlowTest extends WebTestCase
{
    public function testBriefRuleOnCalculator(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $em->createQuery('DELETE FROM App\Pricing\Infrastructure\Doctrine\PricingRuleEntity e')->execute();

        /** @var PricingRuleRepository $repo */
        $repo = $container->get(PricingRuleRepository::class);
        $repo->save(new PricingRuleEntity(
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
        ));

        $client->request('GET', '/rules');
        self::assertResponseIsSuccessful();
        self::assertStringContainsString(
            '10% Monday January advance-booking discount',
            (string) $client->getResponse()->getContent(),
        );

        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Calculate')->form([
            'calculator[activity]' => 'city_tour',
            'calculator[option]' => 'standard',
            'calculator[activityDate]' => '2026-01-12',
            'calculator[bookingDate]' => '2026-01-01',
            'calculator[ticketCategories][0][name]' => 'Adult',
            'calculator[ticketCategories][0][price]' => '100',
            'calculator[ticketCategories][1][name]' => 'Child',
            'calculator[ticketCategories][1][price]' => '50',
        ]);
        $client->submit($form);
        self::assertResponseIsSuccessful();

        $content = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('10% Monday January advance-booking discount', $content);
        self::assertStringContainsString('$90.00', $content);
        self::assertStringContainsString('$45.00', $content);
        self::assertStringContainsString('$135.00', $content);
    }
}
