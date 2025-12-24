<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function provinces()
    {
        $provinces = ShippingZone::select('province')
            ->distinct()
            ->orderBy('province')
            ->get()
            ->pluck('province');

        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    public function getCost(Request $request)
    {
        $request->validate([
            'province' => 'required|string',
            'method' => 'required|in:regular,express',
        ]);

        $zone = ShippingZone::where('province', $request->province)->first();

        if (!$zone) {
            return response()->json([
                'success' => false,
                'message' => 'Province not found.',
            ], 404);
        }

        $cost = $request->method === 'express' ? $zone->cost_express : $zone->cost_regular;

        return response()->json([
            'success' => true,
            'data' => [
                'province' => $zone->province,
                'zone' => $zone->zone,
                'cost' => $cost,
                'method' => $request->method,
            ],
        ]);
    }
}
