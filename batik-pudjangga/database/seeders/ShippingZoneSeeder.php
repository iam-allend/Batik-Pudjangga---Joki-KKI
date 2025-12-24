<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            // Zone 1 - Jabodetabek
            ['province' => 'DKI Jakarta', 'zone' => 1, 'cost_regular' => 12000, 'cost_express' => 22000],
            ['province' => 'Jawa Barat', 'zone' => 1, 'cost_regular' => 12000, 'cost_express' => 22000],
            ['province' => 'Banten', 'zone' => 1, 'cost_regular' => 12000, 'cost_express' => 22000],
            
            // Zone 2 - Jawa Tengah & Timur
            ['province' => 'Jawa Tengah', 'zone' => 2, 'cost_regular' => 15000, 'cost_express' => 25000],
            ['province' => 'DI Yogyakarta', 'zone' => 2, 'cost_regular' => 15000, 'cost_express' => 25000],
            ['province' => 'Jawa Timur', 'zone' => 2, 'cost_regular' => 15000, 'cost_express' => 25000],
            
            // Zone 3 - Sumatera & Bali
            ['province' => 'Aceh', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Sumatera Utara', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Sumatera Barat', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Riau', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Jambi', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Sumatera Selatan', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Bangka Belitung', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Bengkulu', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Lampung', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Kepulauan Riau', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Bali', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Nusa Tenggara Barat', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            ['province' => 'Nusa Tenggara Timur', 'zone' => 3, 'cost_regular' => 25000, 'cost_express' => 35000],
            
            // Zone 4 - Kalimantan & Sulawesi
            ['province' => 'Kalimantan Barat', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Kalimantan Tengah', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Kalimantan Selatan', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Kalimantan Timur', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Kalimantan Utara', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Sulawesi Utara', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Sulawesi Tengah', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Sulawesi Selatan', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Sulawesi Tenggara', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Gorontalo', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            ['province' => 'Sulawesi Barat', 'zone' => 4, 'cost_regular' => 35000, 'cost_express' => 45000],
            
            // Zone 5 - Maluku & Papua
            ['province' => 'Maluku', 'zone' => 5, 'cost_regular' => 50000, 'cost_express' => 65000],
            ['province' => 'Maluku Utara', 'zone' => 5, 'cost_regular' => 50000, 'cost_express' => 65000],
            ['province' => 'Papua', 'zone' => 5, 'cost_regular' => 50000, 'cost_express' => 65000],
            ['province' => 'Papua Barat', 'zone' => 5, 'cost_regular' => 50000, 'cost_express' => 65000],
        ];

        foreach ($zones as $zone) {
            ShippingZone::create($zone);
        }
    }
}