<?php

namespace AHATechnocrats\OmicsLogic\Services;

class CountryLabelResolver
{
    public function resolve(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim($value);
        $countries = config('omicslogic.countries', []);

        if (in_array($value, $countries, true)) {
            return $value;
        }

        foreach ($countries as $country) {
            if (strcasecmp($country, $value) === 0) {
                return $country;
            }
        }

        $isoMap = [
            'IN' => 'India',
            'IND' => 'India',
            'US' => 'United States',
            'USA' => 'United States',
            'GB' => 'United Kingdom',
            'UK' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'DE' => 'Germany',
            'FR' => 'France',
            'PK' => 'Pakistan',
            'NG' => 'Nigeria',
            'IR' => 'Iran',
            'EG' => 'Egypt',
            'CN' => 'China',
            'JP' => 'Japan',
            'BR' => 'Brazil',
            'AE' => 'United Arab Emirates',
        ];

        if (isset($isoMap[strtoupper($value)])) {
            return $isoMap[strtoupper($value)];
        }

        if (strlen($value) <= 3) {
            $matches = array_values(array_filter(
                $countries,
                fn (string $country) => str_starts_with(strtolower($country), strtolower($value)),
            ));

            if ($matches !== []) {
                return $matches[0];
            }
        }

        return $value;
    }
}
