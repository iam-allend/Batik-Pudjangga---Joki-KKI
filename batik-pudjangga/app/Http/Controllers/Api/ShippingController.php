<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function provinces()
    {
        try {
            $provinces = ShippingZone::select('province')
                ->distinct()
                ->orderBy('province')
                ->get()
                ->pluck('province');

            return response()->json([
                'success' => true,
                'data' => $provinces,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load provinces: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getCost(Request $request)
    {
        try {
            $request->validate([
                'province' => 'required|string',
                'method' => 'required|in:regular,express',
            ]);

            $zone = ShippingZone::where('province', $request->province)->first();

            if (!$zone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Shipping zone not found for this province.',
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
                    'formatted_cost' => 'Rp ' . number_format($cost, 0, ',', '.'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate shipping cost: ' . $e->getMessage(),
            ], 500);
        }
    }
}