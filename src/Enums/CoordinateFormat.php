<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Enums;

enum CoordinateFormat: string
{
    case DMS = 'DMS';
    case DDM = 'DDM';
    case UTM = 'UTM';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function toString(): string
    {
        return match ($this) {
            self::DMS => 'Degrees Minutes Seconds',
            self::DDM => 'Degrees Decimal Minutes',
            self::UTM => 'Universal Transverse Mercator',
        };
    }
}
