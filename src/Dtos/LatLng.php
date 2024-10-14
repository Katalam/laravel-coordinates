<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Converter\LatLngToDDM;
use Katalam\Coordinates\Converter\LatLngToDMS;
use Katalam\Coordinates\Converter\LatLngToUTM;
use Katalam\Coordinates\Dtos\Concerns\BaseCoordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

class LatLng extends BaseCoordinate
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

    public function getHemisphereLatitude(): string
    {
        return $this->latitude >= 0 ? 'N' : 'S';
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getHemisphereLongitude(): string
    {
        return $this->longitude >= 0 ? 'E' : 'W';
    }

    public function toString(int $precision = -1): string
    {
        $precision = $this->getPrecision($precision);

        $latitude = round(abs($this->getLatitude()), $precision, PHP_ROUND_HALF_DOWN);
        $longitude = round(abs($this->getLongitude()), $precision, PHP_ROUND_HALF_DOWN);

        return sprintf("%.{$precision}f° %s %.{$precision}f° %s",
            $latitude,
            $this->getHemisphereLatitude(),
            $longitude,
            $this->getHemisphereLongitude(),
        );
    }

    public function convert(CoordinateFormat $format): BaseCoordinate
    {
        return match ($format) {
            CoordinateFormat::DMS => LatLngToDMS::make($this)->run(),
            CoordinateFormat::DDM => LatLngToDDM::make($this)->run(),
            CoordinateFormat::UTM => LatLngToUTM::make($this)->run(),
            default => $this,
        };
    }
}
