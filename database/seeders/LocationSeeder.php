<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Kenya',
                'code' => 'KEN',
                'country' => 'Kenya',
                'timezone' => 'Africa/Nairobi',
                'is_active' => true,
            ],
            [
                'name' => 'Uganda',
                'code' => 'UGA',
                'country' => 'Uganda',
                'timezone' => 'Africa/Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Tanzania',
                'code' => 'TZA',
                'country' => 'Tanzania',
                'timezone' => 'Africa/Dar_es_Salaam',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
