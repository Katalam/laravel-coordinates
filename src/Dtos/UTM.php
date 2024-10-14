<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Converter\UTMToLatLng;
use Katalam\Coordinates\Dtos\Concerns\BaseCoordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

class UTM extends BaseCoordinate
{
    protected int $zone;

    protected string $latitudeBand;

    protected float $easting;

    protected float $northing;

    public function __construct(int $zone = 0, string $latitudeBand = '', float $easting = 0, float $northing = 0)
    {
        $this->zone = $zone;
        $this->latitudeBand = $latitudeBand;
        $this->easting = $easting;
        $this->northing = $northing;
    }

    public static function make(int $zone = 0, string $latitudeBand = '', float $easting = 0, float $northing = 0): self
    {
        return new self($zone, $latitudeBand, $easting, $northing);
    }

    public function getZone(): int
    {
        return $this->zone;
    }

    public function getLatitudeBand(): string
    {
        return $this->latitudeBand;
    }

    public function getEasting(): float
    {
        return $this->easting;
    }

    public function getNorthing(): float
    {
        return $this->northing;
    }

    public function toString(int $precision = -1): string
    {
        $precision = $this->getPrecision($precision);

        return sprintf("%d%s %.{$precision}f %.{$precision}f",
            $this->getZone(),
            $this->getLatitudeBand(),
            $this->getEasting(),
            $this->getNorthing(),
        );
    }

    public function convert(CoordinateFormat $format): BaseCoordinate
    {
        return match ($format) {
            CoordinateFormat::LatLng => UTMToLatLng::make($this)->run(),
            default => $this,
        };
    }
}
