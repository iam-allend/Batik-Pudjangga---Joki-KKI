<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Auth::user()->addresses()->latest()->get();
        return view('profile.addresses', compact('addresses'));
    }

    public function create()
    {
        $provinces = ShippingZone::distinct()->pluck('province')->sort()->values();
        return view('addresses.create', compact('provinces'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();

        // If this is set as default, unset all other defaults
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        if ($user->addresses()->count() == 0) {
            $validated['is_default'] = true;
        }

        $user->addresses()->create($validated);

        return redirect()->route('address.index')
            ->with('success', 'Address added successfully!');
    }

    public function edit(Address $address)
    {
        // Authorize
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $provinces = ShippingZone::distinct()->pluck('province')->sort()->values();
        return view('addresses.edit', compact('address', 'provinces'));
    }

    public function update(Request $request, Address $address)
    {
        // Authorize
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'province' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        // If this is set as default, unset all other defaults
        if ($request->is_default) {
            Auth::user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        return redirect()->route('address.index')
            ->with('success', 'Address updated successfully!');
    }

    public function destroy(Address $address)
    {
        // Authorize
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        // Don't allow deleting if it's the only address
        if (Auth::user()->addresses()->count() == 1) {
            return redirect()->route('address.index')
                ->with('error', 'You must have at least one address!');
        }

        // If deleting default address, set another as default
        if ($address->is_default) {
            $newDefault = Auth::user()->addresses()
                ->where('id', '!=', $address->id)
                ->first();

            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return redirect()->route('address.index')
            ->with('success', 'Address deleted successfully!');
    }

    public function setDefault(Address $address)
    {
        // Authorize
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        // Unset all defaults
        Auth::user()->addresses()->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return redirect()->route('address.index')
            ->with('success', 'Default address updated!');
    }
}
