<?php

declare(strict_types=1);

use Katalam\Coordinates\Dtos\Coordinate;

if (! function_exists('coordinates')) {
    function coordinates(float $latitude = 0, float $longitude = 0): Coordinate
    {
        return new Coordinate($latitude, $longitude);
    }
}
