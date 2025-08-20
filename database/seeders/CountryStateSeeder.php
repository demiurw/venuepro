<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Country;
use App\Models\State;

class CountryStateSeeder extends Seeder
{
    public function run()
    {
        // Create countries
        $countries = [
            ['code' => 'US', 'name' => 'United States', 'phone_code' => '+1'],
            ['code' => 'CA', 'name' => 'Canada', 'phone_code' => '+1'],
            ['code' => 'GB', 'name' => 'United Kingdom', 'phone_code' => '+44'],
            ['code' => 'AU', 'name' => 'Australia', 'phone_code' => '+61'],
            ['code' => 'DE', 'name' => 'Germany', 'phone_code' => '+49'],
            ['code' => 'FR', 'name' => 'France', 'phone_code' => '+33'],
            ['code' => 'JP', 'name' => 'Japan', 'phone_code' => '+81'],
            ['code' => 'SG', 'name' => 'Singapore', 'phone_code' => '+65'],
        ];

        foreach ($countries as $countryData) {
            Country::create($countryData);
        }

        // Get created countries for state references
        $us = Country::where('code', 'US')->first();
        $ca = Country::where('code', 'CA')->first();
        $gb = Country::where('code', 'GB')->first();
        $au = Country::where('code', 'AU')->first();

        // Create US states
        if ($us) {
            $usStates = [
                ['name' => 'Alabama', 'code' => 'AL', 'country_id' => $us->id],
                ['name' => 'Alaska', 'code' => 'AK', 'country_id' => $us->id],
                ['name' => 'Arizona', 'code' => 'AZ', 'country_id' => $us->id],
                ['name' => 'Arkansas', 'code' => 'AR', 'country_id' => $us->id],
                ['name' => 'California', 'code' => 'CA', 'country_id' => $us->id],
                ['name' => 'Colorado', 'code' => 'CO', 'country_id' => $us->id],
                ['name' => 'Connecticut', 'code' => 'CT', 'country_id' => $us->id],
                ['name' => 'Delaware', 'code' => 'DE', 'country_id' => $us->id],
                ['name' => 'Florida', 'code' => 'FL', 'country_id' => $us->id],
                ['name' => 'Georgia', 'code' => 'GA', 'country_id' => $us->id],
                ['name' => 'Hawaii', 'code' => 'HI', 'country_id' => $us->id],
                ['name' => 'Idaho', 'code' => 'ID', 'country_id' => $us->id],
                ['name' => 'Illinois', 'code' => 'IL', 'country_id' => $us->id],
                ['name' => 'Indiana', 'code' => 'IN', 'country_id' => $us->id],
                ['name' => 'Iowa', 'code' => 'IA', 'country_id' => $us->id],
                ['name' => 'Kansas', 'code' => 'KS', 'country_id' => $us->id],
                ['name' => 'Kentucky', 'code' => 'KY', 'country_id' => $us->id],
                ['name' => 'Louisiana', 'code' => 'LA', 'country_id' => $us->id],
                ['name' => 'Maine', 'code' => 'ME', 'country_id' => $us->id],
                ['name' => 'Maryland', 'code' => 'MD', 'country_id' => $us->id],
                ['name' => 'Massachusetts', 'code' => 'MA', 'country_id' => $us->id],
                ['name' => 'Michigan', 'code' => 'MI', 'country_id' => $us->id],
                ['name' => 'Minnesota', 'code' => 'MN', 'country_id' => $us->id],
                ['name' => 'Mississippi', 'code' => 'MS', 'country_id' => $us->id],
                ['name' => 'Missouri', 'code' => 'MO', 'country_id' => $us->id],
                ['name' => 'Montana', 'code' => 'MT', 'country_id' => $us->id],
                ['name' => 'Nebraska', 'code' => 'NE', 'country_id' => $us->id],
                ['name' => 'Nevada', 'code' => 'NV', 'country_id' => $us->id],
                ['name' => 'New Hampshire', 'code' => 'NH', 'country_id' => $us->id],
                ['name' => 'New Jersey', 'code' => 'NJ', 'country_id' => $us->id],
                ['name' => 'New Mexico', 'code' => 'NM', 'country_id' => $us->id],
                ['name' => 'New York', 'code' => 'NY', 'country_id' => $us->id],
                ['name' => 'North Carolina', 'code' => 'NC', 'country_id' => $us->id],
                ['name' => 'North Dakota', 'code' => 'ND', 'country_id' => $us->id],
                ['name' => 'Ohio', 'code' => 'OH', 'country_id' => $us->id],
                ['name' => 'Oklahoma', 'code' => 'OK', 'country_id' => $us->id],
                ['name' => 'Oregon', 'code' => 'OR', 'country_id' => $us->id],
                ['name' => 'Pennsylvania', 'code' => 'PA', 'country_id' => $us->id],
                ['name' => 'Rhode Island', 'code' => 'RI', 'country_id' => $us->id],
                ['name' => 'South Carolina', 'code' => 'SC', 'country_id' => $us->id],
                ['name' => 'South Dakota', 'code' => 'SD', 'country_id' => $us->id],
                ['name' => 'Tennessee', 'code' => 'TN', 'country_id' => $us->id],
                ['name' => 'Texas', 'code' => 'TX', 'country_id' => $us->id],
                ['name' => 'Utah', 'code' => 'UT', 'country_id' => $us->id],
                ['name' => 'Vermont', 'code' => 'VT', 'country_id' => $us->id],
                ['name' => 'Virginia', 'code' => 'VA', 'country_id' => $us->id],
                ['name' => 'Washington', 'code' => 'WA', 'country_id' => $us->id],
                ['name' => 'West Virginia', 'code' => 'WV', 'country_id' => $us->id],
                ['name' => 'Wisconsin', 'code' => 'WI', 'country_id' => $us->id],
                ['name' => 'Wyoming', 'code' => 'WY', 'country_id' => $us->id],
            ];

            foreach ($usStates as $stateData) {
                State::create($stateData);
            }
        }

        // Create Canadian provinces
        if ($ca) {
            $caProvinces = [
                ['name' => 'Alberta', 'code' => 'AB', 'country_id' => $ca->id],
                ['name' => 'British Columbia', 'code' => 'BC', 'country_id' => $ca->id],
                ['name' => 'Manitoba', 'code' => 'MB', 'country_id' => $ca->id],
                ['name' => 'New Brunswick', 'code' => 'NB', 'country_id' => $ca->id],
                ['name' => 'Newfoundland and Labrador', 'code' => 'NL', 'country_id' => $ca->id],
                ['name' => 'Northwest Territories', 'code' => 'NT', 'country_id' => $ca->id],
                ['name' => 'Nova Scotia', 'code' => 'NS', 'country_id' => $ca->id],
                ['name' => 'Nunavut', 'code' => 'NU', 'country_id' => $ca->id],
                ['name' => 'Ontario', 'code' => 'ON', 'country_id' => $ca->id],
                ['name' => 'Prince Edward Island', 'code' => 'PE', 'country_id' => $ca->id],
                ['name' => 'Quebec', 'code' => 'QC', 'country_id' => $ca->id],
                ['name' => 'Saskatchewan', 'code' => 'SK', 'country_id' => $ca->id],
                ['name' => 'Yukon', 'code' => 'YT', 'country_id' => $ca->id],
            ];

            foreach ($caProvinces as $provinceData) {
                State::create($provinceData);
            }
        }

        // Create UK countries/regions
        if ($gb) {
            $gbRegions = [
                ['name' => 'England', 'code' => 'ENG', 'country_id' => $gb->id],
                ['name' => 'Scotland', 'code' => 'SCT', 'country_id' => $gb->id],
                ['name' => 'Wales', 'code' => 'WLS', 'country_id' => $gb->id],
                ['name' => 'Northern Ireland', 'code' => 'NIR', 'country_id' => $gb->id],
            ];

            foreach ($gbRegions as $regionData) {
                State::create($regionData);
            }
        }

        // Create Australian states
        if ($au) {
            $auStates = [
                ['name' => 'Australian Capital Territory', 'code' => 'ACT', 'country_id' => $au->id],
                ['name' => 'New South Wales', 'code' => 'NSW', 'country_id' => $au->id],
                ['name' => 'Northern Territory', 'code' => 'NT', 'country_id' => $au->id],
                ['name' => 'Queensland', 'code' => 'QLD', 'country_id' => $au->id],
                ['name' => 'South Australia', 'code' => 'SA', 'country_id' => $au->id],
                ['name' => 'Tasmania', 'code' => 'TAS', 'country_id' => $au->id],
                ['name' => 'Victoria', 'code' => 'VIC', 'country_id' => $au->id],
                ['name' => 'Western Australia', 'code' => 'WA', 'country_id' => $au->id],
            ];

            foreach ($auStates as $stateData) {
                State::create($stateData);
            }
        }

        $this->command->info('Countries and states seeded successfully!');
    }
}