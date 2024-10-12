<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

readonly class LatLngToUTM
{
    public const UTM_SCALE_CENTRAL_MERIDIAN = 0.9996;

    public const EQUATORIAL_RADIUS = 6378137;

    public const MAGNITUDE_OF_FLATTENING = 1 / 298.257223563;

    public const LATITUDE_BANDS = 'CDEFGHJKLMNPQRSTUVWXX'; // X is used for 80°N to 84°N

    public function __construct(
        private float $latitude,
        private float $longitude,
        private int $precision = -1,
    ) {}

    public static function make(float $latitude, float $longitude, int $precision = -1): self
    {
        return new self($latitude, $longitude, $precision);
    }

    /**
     * Convert Latitude and Longitude to UTM
     *
     * Implementation of Karney's method, using Krüger series to order 6.
     * Giving results accurate to 5 nm for distances up to 3900 km from the central meridian.
     *
     * @see https://www.mygeodesy.id.au/documents/Karney-Krueger%20equations.pdf
     */
    public function run(): string
    {
        /*
         * Zone Number = [ Longitude + 180  / 6 ] + 1;
         * Example:
         * Longitude = 103.853612
         * Zone Number = [ 103.853612 + 180  / 6 ] + 1 = 48
         */
        $zone = floor(($this->longitude + 180) / 6) + 1;

        /*
         * Latitude Band = (int) ( Latitude / 8 ) + 10;
         * Example:
         * Latitude = 1.366666
         * Latitude Band = (int) ( 1.366666 / 8 ) + 10 = 10
         * Latitude Band = J
         */
        $indexLatitudeBand = (int) floor($this->latitude / 8 + 10);
        $letter = self::LATITUDE_BANDS[$indexLatitudeBand];

        $zone = (int) floor(($this->longitude + 180) / 6) + 1;

        // + 3 puts origin in the middle of the zone
        $zoneCentralMeridian = deg2rad(($zone - 1) * 6 - 180 + 3);

        // Handle the special case of Norway
        if ($zone === 31 && $letter === 'V' && $this->longitude >= 3) {
            $zone++;
            $zoneCentralMeridian += deg2rad(6);
        }
        // Handle the special case of Svalbard
        if ($zone === 32 && $letter === 'X' && $this->longitude < 9) {
            $zone--;
            $zoneCentralMeridian -= deg2rad(6);
        }
        if ($zone === 32 && $letter === 'X' && $this->longitude >= 9) {
            $zone++;
            $zoneCentralMeridian += deg2rad(6);
        }
        if ($zone === 34 && $letter === 'X' && $this->longitude < 21) {
            $zone--;
            $zoneCentralMeridian -= deg2rad(6);
        }
        if ($zone === 34 && $letter === 'X' && $this->longitude >= 21) {
            $zone++;
            $zoneCentralMeridian += deg2rad(6);
        }
        if ($zone === 36 && $letter === 'X' && $this->longitude < 33) {
            $zone--;
            $zoneCentralMeridian -= deg2rad(6);
        }
        if ($zone === 36 && $letter === 'X' && $this->longitude >= 33) {
            $zone++;
            $zoneCentralMeridian += deg2rad(6);
        }

        $latitudeRad = deg2rad($this->latitude);
        $longitudeRad = deg2rad($this->longitude) - $zoneCentralMeridian;

        $eccentricity = sqrt(self::MAGNITUDE_OF_FLATTENING * (2 - self::MAGNITUDE_OF_FLATTENING));
        $n = self::MAGNITUDE_OF_FLATTENING / (2 - self::MAGNITUDE_OF_FLATTENING);
        $n2 = $n * $n;
        $n3 = $n2 * $n;
        $n4 = $n3 * $n;
        $n5 = $n4 * $n;
        $n6 = $n5 * $n;

        $cosLongitude = cos($longitudeRad);
        $sinLongitude = sin($longitudeRad);
        $tanLongitude = tan($longitudeRad);

        $tanLatitude = tan($latitudeRad); // tau
        $sigma = sinh($eccentricity * atanh($eccentricity * $tanLatitude / sqrt(1 + $tanLatitude ** 2)));

        $tauPrime = $tanLatitude * sqrt(1 + $sigma ** 2) - $sigma * sqrt(1 + $tanLatitude ** 2);

        $xiPrime = atan2($tauPrime, $cosLongitude);
        $etaPrime = asinh($sinLongitude / sqrt($tauPrime ** 2 + $cosLongitude ** 2));

        // 2πA is the circumference of a meridian
        $A = self::EQUATORIAL_RADIUS / (1 + $n) * (1 + $n2 / 4 + $n4 / 64 + $n6 / 256);

        // note α is one-based array (6th order Krüger expressions)
        $alpha = [
            null,
            $n / 2 - 2 * $n2 / 3 + 5 * $n3 / 16 + 41 * $n4 / 180 - 127 * $n5 / 288 + 7_891 * $n6 / 37_800,
            13 * $n2 / 48 - 3 * $n3 / 5 + 557 * $n4 / 1_440 + 281 * $n5 / 630 - 1_983_433 * $n6 / 1_935_360,
            61 * $n3 / 240 - 103 * $n4 / 140 + 15_061 * $n5 / 26_880 + 167_603 * $n6 / 181_440,
            49_561 * $n4 / 161_280 - 179 * $n5 / 168 + 6_601_661 * $n6 / 7_257_600,
            34_729 * $n5 / 80_640 - 3_418_889 * $n6 / 1_995_840,
            212_378_941 * $n6 / 319_334_400,
        ];

        $xi = $xiPrime;
        for ($i = 1; $i <= 6; $i++) {
            $xi += $alpha[$i] * sin(2 * $i * $xiPrime) * cosh(2 * $i * $etaPrime);
        }
        $eta = $etaPrime;
        for ($i = 1; $i <= 6; $i++) {
            $eta += $alpha[$i] * cos(2 * $i * $xiPrime) * sinh(2 * $i * $etaPrime);
        }

        $x = self::UTM_SCALE_CENTRAL_MERIDIAN * $A * $eta;
        $y = self::UTM_SCALE_CENTRAL_MERIDIAN * $A * $xi;

        // Karney 2011 Eq 23, 24

        $pPrime = 1;
        for ($i = 1; $i <= 6; $i++) {
            $pPrime += 2 * $i * $alpha[$i] * cos(2 * $i * $xiPrime) * cosh(2 * $i * $etaPrime);
        }
        $qPrime = 0;
        for ($i = 1; $i <= 6; $i++) {
            $qPrime += 2 * $i * $alpha[$i] * sin(2 * $i * $xiPrime) * sinh(2 * $i * $etaPrime);
        }

        $gammaPrime = atan($tauPrime / sqrt(1 + $tauPrime ** 2) * $tanLongitude);
        $gammaPrimePrime = atan2($qPrime, $pPrime);

        $gamma = $gammaPrime + $gammaPrimePrime;

        // Karney 2011 Eq 25

        $sinLatitude = sin($latitudeRad);
        $kPrime = sqrt(1 - self::MAGNITUDE_OF_FLATTENING ** 2 * $sinLatitude ** 2) * sqrt(1 + $tanLatitude ** 2) / sqrt($tauPrime ** 2 + $cosLongitude ** 2);
        $kPrimePrime = $A / self::EQUATORIAL_RADIUS * sqrt($pPrime ** 2 + $qPrime ** 2);

        $k = self::UTM_SCALE_CENTRAL_MERIDIAN * $kPrime * $kPrimePrime;

        // shift x/y to UTM grid
        $x += 500_000;
        if ($y < 0) {
            $y += 10_000_000;
        }

        $precision = $this->precision > -1 ? $this->precision : 9;

        $x = round($x, $precision, PHP_ROUND_HALF_DOWN);
        $y = round($y, $precision, PHP_ROUND_HALF_DOWN);

        return sprintf(
            "%d%s %.{$precision}f %.{$precision}f",
            $zone,
            $letter,
            $x,
            $y,
        );
    }
}