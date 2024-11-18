<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Converter\GeoHashToLatLng;
use Katalam\Coordinates\Dtos\Concerns\BaseCoordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

class GeoHash extends BaseCoordinate
{
    protected string $hash;

    public function __construct(string $hash = '')
    {
        $this->hash = $hash;
    }

    public static function make(string $hash = ''): self
    {
        return new self($hash);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function toString(int $precision = 12): string
    {
        $precision = $this->getPrecision($precision);

        return substr($this->hash, 0, $precision);
    }

    public function convert(CoordinateFormat $format): BaseCoordinate
    {
        return match ($format) {
            CoordinateFormat::LatLng => GeoHashToLatLng::make($this)->run(),
            default => $this,
        };
    }
}
