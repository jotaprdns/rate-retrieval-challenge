<?php

namespace App\Helpers;

class RateHelper
{
    /**
     * @param array $rates  Lista como la que devuelve RateService::getRates()
     * @return array        Lista de rates más baratos por "SERVICE LEVEL"
     */
    public static function getCheapestRatesByServiceLevel(array $rates): array
    {
        $byServiceLevel = [];

        foreach ($rates as $rate) {
            $serviceLevel = $rate['SERVICE LEVEL'] ?? '';

            if ($serviceLevel === '') {
                continue;
            }

            if (!isset($byServiceLevel[$serviceLevel])) {
                $byServiceLevel[$serviceLevel] = $rate;
                continue;
            }

            if ($rate['TOTAL'] < $byServiceLevel[$serviceLevel]['TOTAL']) {
                $byServiceLevel[$serviceLevel] = $rate;
            }
        }

        // Devolvemos un array indexado simple con los más baratos
        return array_values($byServiceLevel);
    }
}