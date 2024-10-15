<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Dtos\Concerns\BaseCoordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

class Coordinate
{
    protected BaseCoordinate $value;

    public function __construct(BaseCoordinate $value)
    {
        $this->value = $value;
    }

    public static function make(BaseCoordinate $value): self
    {
        return new self($value);
    }

    public static function fromLatLng(float $latitude, float $longitude): self
    {
        return new self(new LatLng($latitude, $longitude));
    }

    public static function fromDDM(int $degreesLatitude, float $minutesLatitude, string $hemisphereLatitude, int $degreesLongitude, int $minutesLongitude, string $hemisphereLongitude): self
    {
        return new self(new DDM($degreesLatitude, $minutesLatitude, $hemisphereLatitude, $degreesLongitude, $minutesLongitude, $hemisphereLongitude));
    }

    public static function fromDMS(int $degreesLatitude, int $minutesLatitude, float $secondsLatitude, string $hemisphereLatitude, int $degreesLongitude, int $minutesLongitude, float $secondsLongitude, string $hemisphereLongitude): self
    {
        return new self(new DMS($degreesLatitude, $minutesLatitude, $secondsLatitude, $hemisphereLatitude, $degreesLongitude, $minutesLongitude, $secondsLongitude, $hemisphereLongitude));
    }

    public static function fromUTM(int $zone, string $band, float $easting, float $northing): self
    {
        return new self(new UTM($zone, $band, $easting, $northing));
    }

    public function format(CoordinateFormat $format, int $precision = -1): string
    {
        return $this->value->format($format, $precision);
    }

    public function convert(CoordinateFormat $format): self
    {
        $this->value = $this->value->convert($format);

        return $this;
    }

    public function getValue(): BaseCoordinate
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(int $precision = -1): string
    {
        return $this->value->toString($precision);
    }
}
