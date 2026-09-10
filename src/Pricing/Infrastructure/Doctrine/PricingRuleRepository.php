<?php

declare(strict_types=1);

namespace App\Pricing\Infrastructure\Doctrine;

use App\Pricing\Application\PricingRuleRepositoryInterface;
use App\Pricing\Domain\Model\PricingContext;
use App\Pricing\Domain\Model\PricingRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

/**
 * @extends ServiceEntityRepository<PricingRuleEntity>
 */
#[AsAlias(PricingRuleRepositoryInterface::class)]
class PricingRuleRepository extends ServiceEntityRepository implements PricingRuleRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly PricingRuleFactory $factory,
    ) {
        parent::__construct($registry, PricingRuleEntity::class);
    }

    public function findCandidates(PricingContext $context): array
    {
        $activityDate = $context->availability()->dateTime()->setTime(0, 0);
        $daysInAdvance = $context->daysInAdvance();

        /** @var list<PricingRuleEntity> $entities */
        $entities = $this->createQueryBuilder('r')
            ->andWhere('r.activity IS NULL OR r.activity = :activity')
            ->andWhere('r.activityOption IS NULL OR r.activityOption = :activityOption')
            ->andWhere('r.dateFrom IS NULL OR r.dateFrom <= :activityDate')
            ->andWhere('r.dateTo IS NULL OR r.dateTo >= :activityDate')
            ->andWhere('r.minAdvanceDays IS NULL OR r.minAdvanceDays <= :daysInAdvance')
            ->setParameter('activity', $context->availability()->activity()->value)
            ->setParameter('activityOption', $context->availability()->option()->value)
            ->setParameter('activityDate', $activityDate)
            ->setParameter('daysInAdvance', $daysInAdvance)
            ->orderBy('r.priority', 'DESC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (PricingRuleEntity $entity): PricingRule => $this->factory->fromEntity($entity),
            $entities,
        );
    }

    public function findAllOrdered(): array
    {
        /** @var list<PricingRuleEntity> $entities */
        $entities = $this->createQueryBuilder('r')
            ->orderBy('r.priority', 'DESC')
            ->addOrderBy('r.id', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            fn (PricingRuleEntity $entity): PricingRule => $this->factory->fromEntity($entity),
            $entities,
        );
    }

    public function save(PricingRuleEntity $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
    }
}
