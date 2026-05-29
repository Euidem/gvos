<?php

namespace App\Support;

class CountryList
{
    /**
     * Returns a keyed array of country values => display labels.
     * Used in Filament Select and Blade <select> dropdowns.
     */
    public static function options(): array
    {
        return [
            'Nigeria'              => 'Nigeria',
            'United Kingdom'       => 'United Kingdom',
            'United States'        => 'United States',
            'Canada'               => 'Canada',
            'Australia'            => 'Australia',
            'Germany'              => 'Germany',
            'France'               => 'France',
            'Netherlands'          => 'Netherlands',
            'Ireland'              => 'Ireland',
            'Spain'                => 'Spain',
            'Italy'                => 'Italy',
            'South Africa'         => 'South Africa',
            'Ghana'                => 'Ghana',
            'Kenya'                => 'Kenya',
            'United Arab Emirates' => 'United Arab Emirates',
            'India'                => 'India',
            'Pakistan'             => 'Pakistan',
            'Philippines'          => 'Philippines',
            'Brazil'               => 'Brazil',
            'Mexico'               => 'Mexico',
            'Other'                => 'Other',
        ];
    }
}
