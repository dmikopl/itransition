<?php

declare(strict_types=1);

namespace App\Pricing\Domain\Enum;

enum Activity: string
{
    case CityTour = 'city_tour';
    case MuseumVisit = 'museum_visit';
    case BoatCruise = 'boat_cruise';

    public function label(): string
    {
        return match ($this) {
            self::CityTour => 'City tour',
            self::MuseumVisit => 'Museum visit',
            self::BoatCruise => 'Boat cruise',
        };
    }
}
