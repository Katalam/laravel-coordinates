<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

readonly class LatLngToDMS
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
        [$degreesLat, $minutesLat, $secondsLat] = $this->convert($this->latitude);
        [$degreesLng, $minutesLng, $secondsLng] = $this->convert($this->longitude);

        if ($this->precision >= 0) {
            $secondsLat = $this->round($secondsLat, $this->precision);
            $secondsLng = $this->round($secondsLng, $this->precision);
        }

        $precision = $this->precision > -1 ? $this->precision : 6;

        return sprintf(
            "%d°%d'%.{$precision}f\" %s, %d°%d'%.{$precision}f\" %s",
            $degreesLat,
            $minutesLat,
            $secondsLat,
            $this->latitude >= 0 ? 'N' : 'S',
            $degreesLng,
            $minutesLng,
            $secondsLng,
            $this->longitude >= 0 ? 'E' : 'W'
        );
    }

    private function convert(float $coordinate): array
    {
        $degree = floor($coordinate);
        $minutes = floor(($coordinate - $degree) * 60);
        $seconds = ($coordinate - $degree - $minutes / 60) * 3_600;

        return [$degree, $minutes, $seconds];
    }

    private function round(float $value, int $precision): float
    {
        return round($value, $precision, PHP_ROUND_HALF_DOWN);
    }
}
