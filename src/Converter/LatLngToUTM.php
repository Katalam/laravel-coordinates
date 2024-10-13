<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

readonly class LatLngToUTM
{
    public const UTM_SCALE_CENTRAL_MERIDIAN = 0.999_6;

    public const EQUATORIAL_RADIUS = 6_378_137;

    public const MAGNITUDE_OF_FLATTENING = 1 / 298.257_223_563;

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
     * Implementation of Karney's method, using Krüger series to order 8.
     * Giving results accurate to 5 nm for distances up to 3900 km from the central meridian.
     *
     * @see https://www.mygeodesy.id.au/documents/Karney-Krueger%20equations.pdf
     */
    public function run(): string
    {
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

        // 1. Compute the ellipsoidal parameters
        $eccentricity = sqrt(self::MAGNITUDE_OF_FLATTENING * (2 - self::MAGNITUDE_OF_FLATTENING));
        $n = self::MAGNITUDE_OF_FLATTENING / (2 - self::MAGNITUDE_OF_FLATTENING);

        $n2 = $n * $n;
        $n3 = $n2 * $n;
        $n4 = $n3 * $n;
        $n5 = $n4 * $n;
        $n6 = $n5 * $n;
        $n7 = $n6 * $n;
        $n8 = $n7 * $n;

        // 2. Compute the rectifying radius A
        $A = self::EQUATORIAL_RADIUS / (1 + $n) * (1 + $n2 / 4 + $n4 / 64 + $n6 / 256 + 25 * $n8 / 16_384);

        // note α is one-based array (8th order Krüger expressions)
        // 3. Compute the coefficients α to 8th order
        $alpha = [
            null,
            $n / 2 - 2 * $n2 / 3 + 5 * $n3 / 16 + 41 * $n4 / 180 - 127 * $n5 / 288 + 7_891 * $n6 / 37_800,
            13 * $n2 / 48 - 3 * $n3 / 5 + 557 * $n4 / 1_440 + 281 * $n5 / 630 - 1_983_433 * $n6 / 1_935_360,
            61 * $n3 / 240 - 103 * $n4 / 140 + 15_061 * $n5 / 26_880 + 167_603 * $n6 / 181_440,
            49_561 * $n4 / 161_280 - 179 * $n5 / 168 + 6_601_661 * $n6 / 7_257_600,
            34_729 * $n5 / 80_640 - 3_418_889 * $n6 / 1_995_840,
            212_378_941 * $n6 / 319_334_400 - 30_705_481 * $n7 / 10_378_368 + 2_605_413_599 * $n8 / 58_118_860_800,
            1_522_256_789 * $n7 / 1_383_782_400 - 16_759_934_899 * $n8 / 3_113_510_400,
            1_424_729_850_961 * $n8 / 743_921_418_240,
        ];

        // 4. Compute the conformal latitude
        $tanLatitude = tan($latitudeRad); // tau
        $sigma = sinh($eccentricity * atanh($eccentricity * $tanLatitude / sqrt(1 + $tanLatitude ** 2)));
        $tauPrime = $tanLatitude * sqrt(1 + $sigma ** 2) - $sigma * sqrt(1 + $tanLatitude ** 2);

        // 5. Compute the longitude difference
        $longitudeRad = deg2rad($this->longitude) - $zoneCentralMeridian;

        // 6. Compute the Gauss-Schreiber ratios xi' = X' / A and eta' = Y' / A to order 8
        $cosLongitude = cos($longitudeRad);
        $sinLongitude = sin($longitudeRad);
        $xiPrime = atan2($tauPrime, $cosLongitude);
        $etaPrime = asinh($sinLongitude / sqrt($tauPrime ** 2 + $cosLongitude ** 2));

        // 7. Compute the transverse Mercator (TM) ratios xi = X / A and eta = Y / A to order 8
        $xi = $xiPrime;
        for ($i = 1; $i <= 8; $i++) {
            $xi += $alpha[$i] * sin(2 * $i * $xiPrime) * cosh(2 * $i * $etaPrime);
        }
        $eta = $etaPrime;
        for ($i = 1; $i <= 8; $i++) {
            $eta += $alpha[$i] * cos(2 * $i * $xiPrime) * sinh(2 * $i * $etaPrime);
        }

        // 8. Compute the TM coordinates x, y
        $x = $A * $eta;
        $y = $A * $xi;

        // 9. Compute grid coordinates e, n
        $e = self::UTM_SCALE_CENTRAL_MERIDIAN * $x + 500_000;
        $n = self::UTM_SCALE_CENTRAL_MERIDIAN * $y + ($this->latitude < 0 ? 10_000_000 : 0);

        $precision = $this->precision > -1 ? $this->precision : 9;

        $e = round($e, $precision, PHP_ROUND_HALF_DOWN);
        $n = round($n, $precision, PHP_ROUND_HALF_DOWN);

        // 10. Compute factors p and 1 to order 8
        // $pPrime = 1;
        // for ($i = 1; $i <= 8; $i++) {
        //    $pPrime += 2 * $i * $alpha[$i] * cos(2 * $i * $xiPrime) * cosh(2 * $i * $etaPrime);
        // }
        // $qPrime = 0;
        // for ($i = 1; $i <= 8; $i++) {
        //    $qPrime += 2 * $i * $alpha[$i] * sin(2 * $i * $xiPrime) * sinh(2 * $i * $etaPrime);
        // }

        // 11. Compute point scale factor
        // $sinLatitude = sin($latitudeRad);
        // $kPrime = sqrt(1 + $tanLatitude ** 2) * sqrt(1 - self::MAGNITUDE_OF_FLATTENING ** 2 * $sinLatitude ** 2) / sqrt($tauPrime ** 2 + $cosLongitude ** 2);
        // $kPrimePrime = $A / self::EQUATORIAL_RADIUS * sqrt($pPrime ** 2 + $qPrime ** 2);
        // $k = self::UTM_SCALE_CENTRAL_MERIDIAN * $kPrime * $kPrimePrime;

        // 12. Compute grid convergence
        // $tanLongitude = tan($longitudeRad);
        // $gammaPrime = atan($tauPrime * $tanLongitude / sqrt(1 + $tauPrime ** 2));
        // $gammaPrimePrime = atan2($qPrime, $pPrime);
        // $gamma = $gammaPrime + $gammaPrimePrime;

        return sprintf(
            "%2d%s %.{$precision}f %.{$precision}f",
            $zone,
            $letter,
            $e,
            $n,
        );
    }
}
