<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

use Katalam\Coordinates\Dtos\GeoHash;
use Katalam\Coordinates\Dtos\LatLng;

readonly class LatLngToGeoHash
{
    public const HASH_MAP = '0123456789bcdefghjkmnpqrstuvwxyz';

    public function __construct(
        private LatLng $latLng,
    ) {}

    public static function make(LatLng $latLng): self
    {
        return new self($latLng);
    }

    public function run(): GeoHash
    {
        $index = 0;
        $bit = 0;
        $even = true;
        $geoHash = '';

        $lat = [
            'min' => -90.0,
            'max' => 90.0,
        ];

        $lng = [
            'min' => -180.0,
            'max' => 180.0,
        ];

        while (strlen($geoHash) < 12) {
            if ($even) {
                $mid = ($lng['min'] + $lng['max']) / 2;
                if ($this->latLng->getLongitude() > $mid) {
                    $index = ($index << 1) + 1;
                    $lng['min'] = $mid;
                } else {
                    $index <<= 1;
                    $lng['max'] = $mid;
                }
            } else {
                $mid = ($lat['min'] + $lat['max']) / 2;
                if ($this->latLng->getLatitude() > $mid) {
                    $index = ($index << 1) + 1;
                    $lat['min'] = $mid;
                } else {
                    $index <<= 1;
                    $lat['max'] = $mid;
                }
            }

            $even = ! $even;
            if (++$bit === 5) {
                $geoHash .= self::HASH_MAP[$index];
                $bit = 0;
                $index = 0;
            }
        }

        return new GeoHash($geoHash);
    }
}
