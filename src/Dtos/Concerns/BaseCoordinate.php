<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos\Concerns;

use Katalam\Coordinates\Enums\CoordinateFormat;

abstract class BaseCoordinate
{
    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function format(CoordinateFormat $format, int $precision = -1): string
    {
        return $this->convert($format)->toString($precision);
    }

    protected function getPrecision(int $givenPrecision): int
    {
        return $givenPrecision > -1 ? $givenPrecision : 6;
    }

    abstract public function toString(int $precision = -1): string;

    abstract public function convert(CoordinateFormat $format): self;
}
