<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            // Remote Options
            [
                'city' => 'Remote',
                'state' => 'Global',
                'country' => 'Global',
            ],
            [
                'city' => 'Hybrid',
                'state' => 'Global',
                'country' => 'Global',
            ],

            // United States
            [
                'city' => 'San Francisco',
                'state' => 'CA',
                'country' => 'United States',
            ],
            [
                'city' => 'San Jose',
                'state' => 'CA',
                'country' => 'United States',
            ],
            [
                'city' => 'Mountain View',
                'state' => 'CA',
                'country' => 'United States',
            ],
            [
                'city' => 'Palo Alto',
                'state' => 'CA',
                'country' => 'United States',
            ],
            [
                'city' => 'Seattle',
                'state' => 'WA',
                'country' => 'United States',
            ],
            [
                'city' => 'Bellevue',
                'state' => 'WA',
                'country' => 'United States',
            ],
            [
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'United States',
            ],
            [
                'city' => 'Boston',
                'state' => 'MA',
                'country' => 'United States',
            ],
            [
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'United States',
            ],
            [
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'United States',
            ],
            [
                'city' => 'Denver',
                'state' => 'CO',
                'country' => 'United States',
            ],

            // Canada
            [
                'city' => 'Vancouver',
                'state' => 'BC',
                'country' => 'Canada',
            ],
            [
                'city' => 'Toronto',
                'state' => 'ON',
                'country' => 'Canada',
            ],
            [
                'city' => 'Montreal',
                'state' => 'QC',
                'country' => 'Canada',
            ],

            // Europe
            [
                'city' => 'London',
                'state' => 'England',
                'country' => 'United Kingdom',
            ],
            [
                'city' => 'Berlin',
                'state' => 'Berlin',
                'country' => 'Germany',
            ],
            [
                'city' => 'Amsterdam',
                'state' => 'North Holland',
                'country' => 'Netherlands',
            ],
            [
                'city' => 'Dublin',
                'state' => 'Dublin',
                'country' => 'Ireland',
            ],
            [
                'city' => 'Stockholm',
                'state' => 'Stockholm',
                'country' => 'Sweden',
            ],
            [
                'city' => 'Paris',
                'state' => 'Île-de-France',
                'country' => 'France',
            ],

            // Asia
            [
                'city' => 'Singapore',
                'state' => 'Singapore',
                'country' => 'Singapore',
            ],
            [
                'city' => 'Tokyo',
                'state' => 'Tokyo',
                'country' => 'Japan',
            ],
            [
                'city' => 'Seoul',
                'state' => 'Seoul',
                'country' => 'South Korea',
            ],
            [
                'city' => 'Hong Kong',
                'state' => 'Hong Kong',
                'country' => 'China',
            ],
            [
                'city' => 'Shanghai',
                'state' => 'Shanghai',
                'country' => 'China',
            ],
            [
                'city' => 'Bangalore',
                'state' => 'Karnataka',
                'country' => 'India',
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
