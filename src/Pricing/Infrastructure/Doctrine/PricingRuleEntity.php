<?php

declare(strict_types=1);

namespace App\Pricing\Infrastructure\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PricingRuleRepository::class)]
#[ORM\Table(name: 'pricing_rule')]
class PricingRuleEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column]
    private int $priority;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $activity = null;

    #[ORM\Column(name: 'activity_option', length: 64, nullable: true)]
    private ?string $activityOption = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dateFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $dateTo = null;

    /**
     * @var list<int>|null
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $daysOfWeek = null;

    #[ORM\Column(nullable: true)]
    private ?int $minAdvanceDays = null;

    #[ORM\Column(length: 64)]
    private string $adjustmentType;

    #[ORM\Column]
    private int $adjustmentValue;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        int $priority,
        string $adjustmentType,
        int $adjustmentValue,
        ?string $activity = null,
        ?string $activityOption = null,
        ?DateTimeImmutable $dateFrom = null,
        ?DateTimeImmutable $dateTo = null,
        ?array $daysOfWeek = null,
        ?int $minAdvanceDays = null,
    ) {
        $this->name = $name;
        $this->priority = $priority;
        $this->adjustmentType = $adjustmentType;
        $this->adjustmentValue = $adjustmentValue;
        $this->activity = $activity;
        $this->activityOption = $activityOption;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
        $this->daysOfWeek = $daysOfWeek;
        $this->minAdvanceDays = $minAdvanceDays;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getActivity(): ?string
    {
        return $this->activity;
    }

    public function getActivityOption(): ?string
    {
        return $this->activityOption;
    }

    public function getDateFrom(): ?DateTimeImmutable
    {
        return $this->dateFrom;
    }

    public function getDateTo(): ?DateTimeImmutable
    {
        return $this->dateTo;
    }

    /**
     * @return list<int>|null
     */
    public function getDaysOfWeek(): ?array
    {
        return $this->daysOfWeek;
    }

    public function getMinAdvanceDays(): ?int
    {
        return $this->minAdvanceDays;
    }

    public function getAdjustmentType(): string
    {
        return $this->adjustmentType;
    }

    public function getAdjustmentValue(): int
    {
        return $this->adjustmentValue;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}