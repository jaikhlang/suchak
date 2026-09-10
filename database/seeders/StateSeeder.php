<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statesAndUts = [
            // 28 States
            ['name' => 'Andhra Pradesh', 'iso_code' => 'IN-AP', 'type' => 'state', 'capital' => 'Amaravati'],
            ['name' => 'Arunachal Pradesh', 'iso_code' => 'IN-AR', 'type' => 'state', 'capital' => 'Itanagar'],
            ['name' => 'Assam', 'iso_code' => 'IN-AS', 'type' => 'state', 'capital' => 'Dispur'],
            ['name' => 'Bihar', 'iso_code' => 'IN-BR', 'type' => 'state', 'capital' => 'Patna'],
            ['name' => 'Chhattisgarh', 'iso_code' => 'IN-CG', 'type' => 'state', 'capital' => 'Raipur'],
            ['name' => 'Goa', 'iso_code' => 'IN-GA', 'type' => 'state', 'capital' => 'Panaji'],
            ['name' => 'Gujarat', 'iso_code' => 'IN-GJ', 'type' => 'state', 'capital' => 'Gandhinagar'],
            ['name' => 'Haryana', 'iso_code' => 'IN-HR', 'type' => 'state', 'capital' => 'Chandigarh'],
            ['name' => 'Himachal Pradesh', 'iso_code' => 'IN-HP', 'type' => 'state', 'capital' => 'Shimla'],
            ['name' => 'Jharkhand', 'iso_code' => 'IN-JH', 'type' => 'state', 'capital' => 'Ranchi'],
            ['name' => 'Karnataka', 'iso_code' => 'IN-KA', 'type' => 'state', 'capital' => 'Bengaluru'],
            ['name' => 'Kerala', 'iso_code' => 'IN-KL', 'type' => 'state', 'capital' => 'Thiruvananthapuram'],
            ['name' => 'Madhya Pradesh', 'iso_code' => 'IN-MP', 'type' => 'state', 'capital' => 'Bhopal'],
            ['name' => 'Maharashtra', 'iso_code' => 'IN-MH', 'type' => 'state', 'capital' => 'Mumbai'],
            ['name' => 'Manipur', 'iso_code' => 'IN-MN', 'type' => 'state', 'capital' => 'Imphal'],
            ['name' => 'Meghalaya', 'iso_code' => 'IN-ML', 'type' => 'state', 'capital' => 'Shillong'],
            ['name' => 'Mizoram', 'iso_code' => 'IN-MZ', 'type' => 'state', 'capital' => 'Aizawl'],
            ['name' => 'Nagaland', 'iso_code' => 'IN-NL', 'type' => 'state', 'capital' => 'Kohima'],
            ['name' => 'Odisha', 'iso_code' => 'IN-OD', 'type' => 'state', 'capital' => 'Bhubaneswar'],
            ['name' => 'Punjab', 'iso_code' => 'IN-PB', 'type' => 'state', 'capital' => 'Chandigarh'],
            ['name' => 'Rajasthan', 'iso_code' => 'IN-RJ', 'type' => 'state', 'capital' => 'Jaipur'],
            ['name' => 'Sikkim', 'iso_code' => 'IN-SK', 'type' => 'state', 'capital' => 'Gangtok'],
            ['name' => 'Tamil Nadu', 'iso_code' => 'IN-TN', 'type' => 'state', 'capital' => 'Chennai'],
            ['name' => 'Telangana', 'iso_code' => 'IN-TS', 'type' => 'state', 'capital' => 'Hyderabad'],
            ['name' => 'Tripura', 'iso_code' => 'IN-TR', 'type' => 'state', 'capital' => 'Agartala'],
            ['name' => 'Uttar Pradesh', 'iso_code' => 'IN-UP', 'type' => 'state', 'capital' => 'Lucknow'],
            ['name' => 'Uttarakhand', 'iso_code' => 'IN-UK', 'type' => 'state', 'capital' => 'Dehradun'],
            ['name' => 'West Bengal', 'iso_code' => 'IN-WB', 'type' => 'state', 'capital' => 'Kolkata'],

            // 8 Union Territories
            ['name' => 'Andaman and Nicobar Islands', 'iso_code' => 'IN-AN', 'type' => 'union_territory', 'capital' => 'Port Blair'],
            ['name' => 'Chandigarh', 'iso_code' => 'IN-CH', 'type' => 'union_territory', 'capital' => 'Chandigarh'],
            ['name' => 'Dadra and Nagar Haveli and Daman and Diu', 'iso_code' => 'IN-DH', 'type' => 'union_territory', 'capital' => 'Daman'],
            ['name' => 'Delhi (National Capital Territory)', 'iso_code' => 'IN-DL', 'type' => 'union_territory', 'capital' => 'New Delhi'],
            ['name' => 'Jammu and Kashmir', 'iso_code' => 'IN-JK', 'type' => 'union_territory', 'capital' => 'Srinagar / Jammu'],
            ['name' => 'Ladakh', 'iso_code' => 'IN-LA', 'type' => 'union_territory', 'capital' => 'Leh'],
            ['name' => 'Lakshadweep', 'iso_code' => 'IN-LD', 'type' => 'union_territory', 'capital' => 'Kavaratti'],
            ['name' => 'Puducherry', 'iso_code' => 'IN-PY', 'type' => 'union_territory', 'capital' => 'Puducherry'],
        ];

        foreach ($statesAndUts as $item) {
            State::firstOrCreate(
                ['iso_code' => $item['iso_code']],
                [
                    'name' => $item['name'],
                    'type' => $item['type'],
                    'capital' => $item['capital'],
                    'is_active' => true,
                ]
            );
        }
    }
}
