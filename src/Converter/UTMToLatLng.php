<?php

declare(strict_types=1);

namespace Katalam\Coordinates\Converter;

use Katalam\Coordinates\Dtos\LatLng;
use Katalam\Coordinates\Dtos\UTM;

readonly class UTMToLatLng
{
    public const UTM_SCALE_CENTRAL_MERIDIAN = 0.999_6;

    public const EQUATORIAL_RADIUS = 6_378_137;

    public const MAGNITUDE_OF_FLATTENING = 1 / 298.257_223_563;

    public const LATITUDE_BANDS = 'CDEFGHJKLMNPQRSTUVWXX'; // X is used for 80°N to 84°N

    public function __construct(
        private UTM $utm,
    ) {}

    public static function make(UTM $utm): self
    {
        return new self($utm);
    }

    /**
     * Convert Latitude and Longitude to UTM
     *
     * Implementation of Karney's method, using Krüger series to order 8.
     * Giving results accurate to 5 nm for distances up to 3900 km from the central meridian.
     *
     * @see https://www.mygeodesy.id.au/documents/Karney-Krueger%20equations.pdf
     */
    public function run(): LatLng
    {
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

        // Note α is one-based array (8th order Krüger expressions)
        // 3. Compute the coefficients α to 8th order
        // $alpha = [
        //     null,
        //     $n / 2 - 2 * $n2 / 3 + 5 * $n3 / 16 + 41 * $n4 / 180 - 127 * $n5 / 288 + 7_891 * $n6 / 37_800,
        //     13 * $n2 / 48 - 3 * $n3 / 5 + 557 * $n4 / 1_440 + 281 * $n5 / 630 - 1_983_433 * $n6 / 1_935_360,
        //     61 * $n3 / 240 - 103 * $n4 / 140 + 15_061 * $n5 / 26_880 + 167_603 * $n6 / 181_440,
        //     49_561 * $n4 / 161_280 - 179 * $n5 / 168 + 6_601_661 * $n6 / 7_257_600,
        //     34_729 * $n5 / 80_640 - 3_418_889 * $n6 / 1_995_840,
        //     212_378_941 * $n6 / 319_334_400 - 30_705_481 * $n7 / 10_378_368 + 2_605_413_599 * $n8 / 58_118_860_800,
        //     1_522_256_789 * $n7 / 1_383_782_400 - 16_759_934_899 * $n8 / 3_113_510_400,
        //     1_424_729_850_961 * $n8 / 743_921_418_240,
        // ];

        // Note β is one-based array (8th order Krüger expressions)
        // 4. Compute the coefficients β to 8th order
        $beta = [
            null,
            -$n / 2 + 2 * $n2 / 3 - 37 * $n3 / 96 + $n4 / 360 + 81 * $n5 / 512 - 96_199 * $n6 / 604_800 + 5_406_467 * $n7 / 38_707_200 - 7_944_359 * $n8 / 67_737_600,
            -$n2 / 48 - $n3 / 15 + 437 * $n4 / 1_440 - 46 * $n5 / 105 + 111_871 * $n6 / 387_072 - 51_841 * $n7 / 1_209_600 - 24_749_483 * $n8 / 348_364_800,
            -17 * $n3 / 480 + 37 * $n4 / 840 + 209 * $n5 / 4_480 - 5_569 * $n6 / 90_720 - 9_261_899 * $n7 / 58_060_800 + 6_457_463 * $n8 / 17_740_800,
            -4_397 * $n4 / 161_280 + 11 * $n5 / 504 + 830_251 * $n6 / 7_257_600 - 466_511 * $n7 / 2_494_800 - 324_154_477 * $n8 / 7_664_025_600,
            -4_583 * $n5 / 161_280 + 108_847 * $n6 / 3_991_680 + 8_005_831 * $n7 / 63_866_880 - 22_894_433 * $n8 / 124_540_416,
            -20_648_693 * $n6 / 638_668_800 + 16_363_163 * $n7 / 518_918_400 + 2_204_645_983 * $n8 / 12_915_302_400,
            -219_941_297 * $n7 / 5_535_129_600 + 497_323_811 * $n8 / 12_454_041_600,
            -191_773_887_257 * $n8 / 3_719_607_091_200,
        ];

        // 5. Compute the transverse Mercator X, Y coordinates
        $x = ($this->utm->getEasting() - 500_000) / self::UTM_SCALE_CENTRAL_MERIDIAN;
        $indexLetter = strpos(self::LATITUDE_BANDS, $this->utm->getLatitudeBand());
        $isSouth = $indexLetter <= 10;
        $y = ($this->utm->getNorthing() - ($isSouth ? 10_000_000 : 0)) / self::UTM_SCALE_CENTRAL_MERIDIAN;

        // 6. Compute the transverse Mercator (TM) ratio xi and eta
        $xi = $y / $A;
        $eta = $x / $A;

        // 7. Compute the Gauss-Schreiber ratios xi' = X' / A and eta' = Y' / A to order 8
        $xiPrime = $xi;
        for ($i = 1; $i <= 8; $i++) {
            $xiPrime += $beta[$i] * sin(2 * $i * $xi) * cosh(2 * $i * $eta);
        }
        $etaPrime = $eta;
        for ($i = 1; $i <= 8; $i++) {
            $etaPrime += $beta[$i] * cos(2 * $i * $xi) * sinh(2 * $i * $eta);
        }

        // 8. Compute the tan of the conformal latitude
        $tanLatitude = sin($xiPrime) / sqrt(sinh($etaPrime) ** 2 + cos($xiPrime) ** 2);

        // 9. Solve for the conformal latitude by Newton-Raphson iteration
        $latitudeI = $tanLatitude;
        do {
            $sigma = sinh($eccentricity * atanh($eccentricity * $latitudeI / sqrt(1 + $latitudeI ** 2)));
            $tau = $latitudeI * sqrt(1 + $sigma ** 2) - $sigma * sqrt(1 + $latitudeI ** 2);

            $f = $tau - $tanLatitude;
            $fPrime = (sqrt(1 + $sigma ** 2) * sqrt(1 + $latitudeI ** 2) - ($sigma * $latitudeI))
                * ((1 - $eccentricity ** 2) * sqrt(1 + $latitudeI ** 2) / (1 + (1 - $eccentricity ** 2) * $latitudeI ** 2));

            $deltaLatitude = ($f / $fPrime);
            $latitudeI -= $deltaLatitude;
        } while (abs($deltaLatitude) > 1e-12);
        $latitudeRad = atan($latitudeI);

        $longitudeRad = atan2(sinh($etaPrime), cos($xiPrime));

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

        $longitudeOffset = deg2rad(($this->utm->getZone() - 1) * 6 - 180 + 3);
        $longitude = rad2deg($longitudeRad + $longitudeOffset);
        $latitude = rad2deg($latitudeRad);

        return new LatLng($latitude, $longitude);
    }
}
