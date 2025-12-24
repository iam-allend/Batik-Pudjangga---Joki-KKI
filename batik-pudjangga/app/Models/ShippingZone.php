<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'province',
        'zone',
        'cost_regular',
        'cost_express',
    ];

    // Static method to get shipping cost
    public static function getCost($province, $method = 'regular')
    {
        $zone = static::where('province', $province)->first();
        
        if (!$zone) {
            return 0;
        }

        return $method === 'express' ? $zone->cost_express : $zone->cost_regular;
    }
}
