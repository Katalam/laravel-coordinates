<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

use Katalam\Coordinates\Dtos\GeoHash;
use Katalam\Coordinates\Dtos\LatLng;

readonly class GeoHashToLatLng
{
    public const HASH_MAP = '0123456789bcdefghjkmnpqrstuvwxyz';

    public function __construct(
        private GeoHash $geoHash,
    ) {}

    public static function make(GeoHash $geoHash): self
    {
        return new self($geoHash);
    }

    public function run(): LatLng
    {
        $hash = $this->geoHash->getHash();
        $hashLength = strlen($hash);
        $lat = 0;
        $lng = 0;
        $isEven = true;
        $latMin = -90;
        $latMax = 90;
        $lngMin = -180;
        $lngMax = 180;

        for ($i = 0; $i < $hashLength; $i++) {
            $c = $hash[$i];
            $cd = strpos(self::HASH_MAP, $c);
            for ($j = 0; $j < 5; $j++) {
                $mask = 1 << (4 - $j);
                if ($isEven) {
                    $lngMid = ($lngMin + $lngMax) / 2;
                    if ($cd & $mask) {
                        $lngMin = $lngMid;
                    } else {
                        $lngMax = $lngMid;
                    }
                } else {
                    $latMid = ($latMin + $latMax) / 2;
                    if ($cd & $mask) {
                        $latMin = $latMid;
                    } else {
                        $latMax = $latMid;
                    }
                }
                $isEven = ! $isEven;
            }
        }

        $lat = ($latMin + $latMax) / 2;
        $lng = ($lngMin + $lngMax) / 2;

        $lat = round($lat, (int) floor((2 - log($latMax - $latMin)) / M_LN10), PHP_ROUND_HALF_DOWN);
        $lng = round($lng, (int) floor((2 - log($lngMax - $lngMin)) / M_LN10), PHP_ROUND_HALF_DOWN);

        return LatLng::make($lat, $lng);
    }
}
