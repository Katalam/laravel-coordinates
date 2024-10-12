<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

readonly class LatLngToDDM
{
    public function __construct(
        private float $latitude,
        private float $longitude,
        private int $precision = -1,
    ) {}

    public static function make(float $latitude, float $longitude, int $precision = -1): self
    {
        return new self($latitude, $longitude, $precision);
    }

    public function run(): string
    {
        [$degreesLat, $minutesLat] = $this->convert($this->latitude);
        [$degreesLng, $minutesLng] = $this->convert($this->longitude);

        if ($this->precision >= 0) {
            $minutesLat = $this->round($minutesLat, $this->precision);
            $minutesLng = $this->round($minutesLng, $this->precision);
        }

        $precision = $this->precision > -1 ? $this->precision : 6;

        return sprintf(
            "%d°%.{$precision}f' %s, %d°%.{$precision}f' %s",
            $degreesLat,
            $minutesLat,
            $this->latitude >= 0 ? 'N' : 'S',
            $degreesLng,
            $minutesLng,
            $this->longitude >= 0 ? 'E' : 'W'
        );
    }

    private function convert(float $coordinate): array
    {
        $degree = floor($coordinate);
        $minutes = ($coordinate - $degree) * 60;

        return [$degree, $minutes];
    }

    private function round(float $value, int $precision): float
    {
        return round($value, $precision, PHP_ROUND_HALF_DOWN);
    }
}
