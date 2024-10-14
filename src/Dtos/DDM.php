<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Dtos\Concerns\BaseCoordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

class DDM extends BaseCoordinate
{
    protected int $degreesLatitude;

    protected float $minutesLatitude;

    protected string $hemisphereLatitude;

    protected int $degreesLongitude;

    protected float $minutesLongitude;

    protected string $hemisphereLongitude;

    public function __construct(
        int $degreesLatitude = 0,
        float $minutesLatitude = 0,
        string $hemisphereLatitude = 'N',
        int $degreesLongitude = 0,
        float $minutesLongitude = 0,
        string $hemisphereLongitude = 'E',
    ) {
        $this->degreesLatitude = $degreesLatitude;
        $this->minutesLatitude = $minutesLatitude;
        $this->hemisphereLatitude = $hemisphereLatitude;
        $this->degreesLongitude = $degreesLongitude;
        $this->minutesLongitude = $minutesLongitude;
        $this->hemisphereLongitude = $hemisphereLongitude;
    }

    public static function make(
        int $degreesLatitude = 0,
        float $minutesLatitude = 0,
        string $hemisphereLatitude = 'N',
        int $degreesLongitude = 0,
        float $minutesLongitude = 0,
        string $hemisphereLongitude = 'E',
    ): self {
        return new self(
            $degreesLatitude,
            $minutesLatitude,
            $hemisphereLatitude,
            $degreesLongitude,
            $minutesLongitude,
            $hemisphereLongitude
        );
    }

    public function getDegreesLatitude(): int
    {
        return $this->degreesLatitude;
    }

    public function getMinutesLatitude(): float
    {
        return $this->minutesLatitude;
    }

    public function getHemisphereLatitude(): string
    {
        return $this->hemisphereLatitude;
    }

    public function getDegreesLongitude(): int
    {
        return $this->degreesLongitude;
    }

    public function getMinutesLongitude(): float
    {
        return $this->minutesLongitude;
    }

    public function getHemisphereLongitude(): string
    {
        return $this->hemisphereLongitude;
    }

    public function toString(int $precision = -1): string
    {
        $precision = $this->getPrecision($precision);

        $degreesLongitude = $this->getDegreesLongitude();
        $degreesLongitude = str_pad((string) $degreesLongitude, 3, '0', STR_PAD_LEFT);

        return sprintf(
            "%d°%.{$precision}f' %s %s°%.{$precision}f' %s",
            $this->getDegreesLatitude(),
            $this->getMinutesLatitude(),
            $this->getHemisphereLatitude(),
            $degreesLongitude,
            $this->getMinutesLongitude(),
            $this->getHemisphereLongitude(),
        );
    }

    public function convert(CoordinateFormat $format): BaseCoordinate
    {
        return match ($format) {
            default => $this,
        };
    }
}
