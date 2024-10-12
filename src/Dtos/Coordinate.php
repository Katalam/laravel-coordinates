<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Converter\LatLngToDDM;
use Katalam\Coordinates\Converter\LatLngToDMS;
use Katalam\Coordinates\Converter\LatLngToUTM;
use Katalam\Coordinates\Enums\CoordinateFormat;

class Coordinate
{
    protected float $latitude;

    protected float $longitude;

    public function __construct(float $latitude = 0, float $longitude = 0)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public static function make(float $latitude = 0, float $longitude = 0): self
    {
        return new self($latitude, $longitude);
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function format(CoordinateFormat $format, int $precision = -1): string
    {
        return match ($format) {
            CoordinateFormat::DMS => LatLngToDMS::make($this->latitude, $this->longitude, $precision)->run(),
            CoordinateFormat::DDM => LatLngToDDM::make($this->latitude, $this->longitude, $precision)->run(),
            CoordinateFormat::UTM => LatLngToUTM::make($this->latitude, $this->longitude, $precision)->run(),
        };
    }
}
