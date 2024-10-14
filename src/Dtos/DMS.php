<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Dtos;

use Katalam\Coordinates\Dtos\Concerns\BaseCoordinate;
use Katalam\Coordinates\Enums\CoordinateFormat;

class DMS extends BaseCoordinate
{
    protected int $degreesLatitude;

    protected int $minutesLatitude;

    protected float $secondsLatitude;

    protected string $hemisphereLatitude;

    protected int $degreesLongitude;

    protected int $minutesLongitude;

    protected float $secondsLongitude;

    protected string $hemisphereLongitude;

    public function __construct(
        int $degreesLatitude = 0,
        int $minutesLatitude = 0,
        float $secondsLatitude = 0,
        string $hemisphereLatitude = 'N',
        int $degreesLongitude = 0,
        int $minutesLongitude = 0,
        float $secondsLongitude = 0,
        string $hemisphereLongitude = 'E',
    ) {
        $this->degreesLatitude = $degreesLatitude;
        $this->minutesLatitude = $minutesLatitude;
        $this->secondsLatitude = $secondsLatitude;
        $this->hemisphereLatitude = $hemisphereLatitude;
        $this->degreesLongitude = $degreesLongitude;
        $this->minutesLongitude = $minutesLongitude;
        $this->secondsLongitude = $secondsLongitude;
        $this->hemisphereLongitude = $hemisphereLongitude;
    }

    public static function make(
        int $degreesLatitude = 0,
        int $minutesLatitude = 0,
        float $secondsLatitude = 0,
        string $hemisphereLatitude = 'N',
        int $degreesLongitude = 0,
        int $minutesLongitude = 0,
        float $secondsLongitude = 0,
        string $hemisphereLongitude = 'E',
    ): self {
        return new self(
            $degreesLatitude,
            $minutesLatitude,
            $secondsLatitude,
            $hemisphereLatitude,
            $degreesLongitude,
            $minutesLongitude,
            $secondsLongitude,
            $hemisphereLongitude
        );
    }

    public function getDegreesLatitude(): int
    {
        return $this->degreesLatitude;
    }

    public function getMinutesLatitude(): int
    {
        return $this->minutesLatitude;
    }

    public function getSecondsLatitude(): float
    {
        return $this->secondsLatitude;
    }

    public function getHemisphereLatitude(): string
    {
        return $this->hemisphereLatitude;
    }

    public function getDegreesLongitude(): int
    {
        return $this->degreesLongitude;
    }

    public function getMinutesLongitude(): int
    {
        return $this->minutesLongitude;
    }

    public function getSecondsLongitude(): float
    {
        return $this->secondsLongitude;
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
            "%d°%d'%.{$precision}f\" %s %s°%d'%.{$precision}f\" %s",
            $this->getDegreesLatitude(),
            $this->getMinutesLatitude(),
            $this->getSecondsLatitude(),
            $this->getHemisphereLatitude(),
            $degreesLongitude,
            $this->getMinutesLongitude(),
            $this->getSecondsLongitude(),
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
