<?php

declare(strict_types=1);

use Katalam\Coordinates\Dtos\GeoHash;
use Katalam\Coordinates\Enums\CoordinateFormat;

describe('LatLng', function () {
    it('can convert to LatLng', function (string $hash, int $precision, string $expected) {
        $coordinate = GeoHash::make($hash);

        expect($coordinate->format(CoordinateFormat::LatLng, $precision))->toBe($expected);
    })
        ->with([
            ['u33db2m3370m', 12, '52.516253300000° N 13.377625300000° E'],
            ['u33db2m3370', 11, '52.51625300000° N 13.37762500000° E'],
            ['u33db2m337', 10, '52.5162550000° N 13.3776300000° E'],
            ['u33db2m33', 9, '52.516260000° N 13.377640000° E'],
            ['u33db2m3', 8, '52.51630000° N 13.37770000° E'],
            ['u33db2m', 7, '52.5170000° N 13.3780000° E'],
            ['u33db2', 6, '52.517000° N 13.380000° E'],
            ['u33db', 5, '52.54000° N 13.38000° E'],
            ['u33d', 4, '52.5000° N 13.5000° E'],
            ['u33', 3, '53.000° N 13.000° E'],
            ['u3', 2, '53.00° N 20.00° E'],
            ['u', 1, '70.0° N 20.0° E'],
        ]);
});
