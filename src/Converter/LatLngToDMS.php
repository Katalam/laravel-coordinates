<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

use Katalam\Coordinates\Dtos\DMS;
use Katalam\Coordinates\Dtos\LatLng;

readonly class LatLngToDMS
{
    public function __construct(
        private LatLng $latLng,
    ) {}

    public static function make(LatLng $latLng): self
    {
        return new self($latLng);
    }

    public function run(): DMS
    {
        [$degreesLat, $minutesLat, $secondsLat] = $this->convert($this->latLng->getLatitude());
        [$degreesLng, $minutesLng, $secondsLng] = $this->convert($this->latLng->getLongitude());

        return new DMS(
            $degreesLat,
            $minutesLat,
            $secondsLat,
            $this->latLng->getLatitude() >= 0 ? 'N' : 'S',
            $degreesLng,
            $minutesLng,
            $secondsLng,
            $this->latLng->getLongitude() >= 0 ? 'E' : 'W'
        );
    }

    private function convert(float $coordinate): array
    {
        $degree = (int) floor($coordinate);
        $minutes = (int) floor(($coordinate - $degree) * 60);
        $seconds = ($coordinate - $degree - $minutes / 60) * 3_600;

        return [$degree, $minutes, $seconds];
    }
}
