<?php

declare(strict_types=1);

use Katalam\Coordinates\Enums\CoordinateFormat;

it('can return the enum cases', function () {
    $cases = CoordinateFormat::values();

    expect($cases)->toBeArray()
        ->toHaveCount(4)
        ->toEqual([
            'LatLng',
            'DMS',
            'DDM',
            'UTM',
        ]);
});

it('can return the name of the enum case', function (CoordinateFormat $coordinateFormat) {
    expect($coordinateFormat->toString())->toBeString();
})->with(CoordinateFormat::cases());
