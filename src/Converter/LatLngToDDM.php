<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

use Katalam\Coordinates\Dtos\DDM;
use Katalam\Coordinates\Dtos\LatLng;

readonly class LatLngToDDM
{
    public function __construct(
        private LatLng $latLng,
    ) {}

    public static function make(LatLng $latLng): self
    {
        return new self($latLng);
    }

    public function run(): DDM
    {
        [$degreesLat, $minutesLat] = $this->convert($this->latLng->getLatitude());
        [$degreesLng, $minutesLng] = $this->convert($this->latLng->getLongitude());

        return new DDM(
            (int) $degreesLat,
            $minutesLat,
            $this->latLng->getLatitude() >= 0 ? 'N' : 'S',
            (int) $degreesLng,
            $minutesLng,
            $this->latLng->getLongitude() >= 0 ? 'E' : 'W'
        );
    }

    private function convert(float $coordinate): array
    {
        $degree = floor($coordinate);
        $minutes = ($coordinate - $degree) * 60;

        return [$degree, $minutes];
    }
}
