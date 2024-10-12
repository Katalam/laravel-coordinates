<?php

declare(strict_types=1);

use Katalam\Coordinates\Dtos\Coordinate;

describe('coordinate', function () {
    it('should return the correct coordinate', function () {
        $coordinate = new Coordinate(10, 20);

        expect($coordinate->getLatitude())->toBe(10.0)
            ->and($coordinate->getLongitude())->toBe(20.0);
    });

    it('should return the default coordinate', function () {
        $coordinate = new Coordinate;

        expect($coordinate->getLatitude())->toBe(0.0)
            ->and($coordinate->getLongitude())->toBe(0.0);
    });

    it('should return a coordinate object', function () {
        $coordinate = new Coordinate;

        expect($coordinate)->toBeInstanceOf(Coordinate::class);
    });
});
