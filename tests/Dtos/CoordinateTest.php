<?php

declare(strict_types=1);

use Katalam\Coordinates\Dtos\Coordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

describe('DMS', function () {
    it('can convert', function (float $lat, float $long, int $precision, string $expected) {
        $coordinate = Coordinate::make($lat, $long);

        expect($coordinate->format(CoordinateFormat::DMS, $precision))->toBe($expected);
    })
        ->with([
            [52.51625340334874, 13.377625381177886, -1, '52°30\'58.512252" N, 13°22\'39.451372" E'],
            [52.51625340334874, 13.377625381177886, 10, '52°30\'58.5122520555" N, 13°22\'39.4513722404" E'],
            [52.51625340334874, 13.377625381177886, 5, '52°30\'58.51225" N, 13°22\'39.45137" E'],
            [52.51625340334874, 13.377625381177886, 4, '52°30\'58.5123" N, 13°22\'39.4514" E'],
            [52.51625340334874, 13.377625381177886, 3, '52°30\'58.512" N, 13°22\'39.451" E'],
            [52.51625340334874, 13.377625381177886, 2, '52°30\'58.51" N, 13°22\'39.45" E'],
            [52.51625340334874, 13.377625381177886, 1, '52°30\'58.5" N, 13°22\'39.5" E'],
            [52.51625340334874, 13.377625381177886, 0, '52°30\'59" N, 13°22\'39" E'],
        ]);
});

describe('DDM', function () {
    it('can convert', function (float $lat, float $long, int $precision, string $expected) {
        $coordinate = Coordinate::make($lat, $long);

        expect($coordinate->format(CoordinateFormat::DDM, $precision))->toBe($expected);
    })
        ->with([
            [52.51625340334874, 13.377625381177886, -1, '52°30.975204\' N, 13°22.657523\' E'],
            [52.51625340334874, 13.377625381177886, 10, '52°30.9752042009\' N, 13°22.6575228707\' E'],
            [52.51625340334874, 13.377625381177886, 5, '52°30.97520\' N, 13°22.65752\' E'],
            [52.51625340334874, 13.377625381177886, 4, '52°30.9752\' N, 13°22.6575\' E'],
            [52.51625340334874, 13.377625381177886, 3, '52°30.975\' N, 13°22.658\' E'],
            [52.51625340334874, 13.377625381177886, 2, '52°30.98\' N, 13°22.66\' E'],
            [52.51625340334874, 13.377625381177886, 1, '52°31.0\' N, 13°22.7\' E'],
            [52.51625340334874, 13.377625381177886, 0, '52°31\' N, 13°23\' E'],
        ]);
});

describe('UTM', function () {
    it('can convert to utm', function (float $lat, float $long, int $precision, string $expected) {
        $coordinate = Coordinate::make($lat, $long);

        expect($coordinate->format(CoordinateFormat::UTM, $precision))->toBe($expected);
    })
        ->with([
            [52.51625340334874, 13.377625381177886, -1, '33U 389912.653201401 5819696.850323285'],
            [52.51625340334874, 13.377625381177886, 10, '33U 389912.6532014008 5819696.8503232850'],
            [52.51625340334874, 13.377625381177886, 1, '33U 389912.7 5819696.9'],
            [52.51625340334874, 13.377625381177886, 2, '33U 389912.65 5819696.85'],
            [52.51625340334874, 13.377625381177886, 3, '33U 389912.653 5819696.850'],
            [52.51625340334874, 13.377625381177886, 4, '33U 389912.6532 5819696.8503'],
        ]);

    it('can convert from utm', function (float $expectedLat, float $expectedLng, string $utm) {
        $coordinate = Coordinate::fromUTM($utm);

        expect($coordinate->getLatitude())->toBe($expectedLat)
            ->and($coordinate->getLongitude())->toBe($expectedLng);
    })
        ->with([
            [52.516253, 13.377625, '33U 389912.6532014008 5819696.8503232850'],
            [-17.978733, 18.457031, '34K 230690.325 8010321.788'],
        ]);
});
