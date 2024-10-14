<?php

declare(strict_types=1);

use Katalam\Coordinates\Dtos\Coordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

describe('UTM', function () {
    it('can convert from utm', function (string $expected, int $zone, string $band, float $utmX, float $utmY) {
        $coordinate = Coordinate::fromUTM($zone, $band, $utmX, $utmY);

        $coordinate = $coordinate->format(CoordinateFormat::LatLng);

        expect($coordinate)->toBe($expected);
    })
        ->with([
            ['52.516253° N 13.377625° E', 33, 'U', 389_912.653_201_400_8, 5_819_696.850_323_285_0],
            ['17.978733° S 18.457031° E', 34, 'K', 230_690.325, 8_010_321.788],
        ]);
});
