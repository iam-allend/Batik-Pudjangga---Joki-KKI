<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingZone;
use Illuminate\Http\Request;

class ShippingZoneController extends Controller
{
    public function index()
    {
        $zones = ShippingZone::orderBy('zone')->orderBy('province')->get();
        return view('admin.shipping-zones.index', compact('zones'));
    }

    public function edit(ShippingZone $shippingZone)
    {
        return view('admin.shipping-zones.edit', compact('shippingZone'));
    }

    public function update(Request $request, ShippingZone $shippingZone)
    {
        $request->validate([
            'cost_regular' => 'required|integer|min:0',
            'cost_express' => 'required|integer|min:0',
        ]);

        $shippingZone->update($request->only('cost_regular', 'cost_express'));

        return redirect()->route('admin.shipping-zones.index')
            ->with('success', 'Shipping zone updated successfully!');
    }
}
