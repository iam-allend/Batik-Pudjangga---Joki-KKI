<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses;
        return view('profile.addresses', compact('addresses'));
    }

    public function create()
    {
        return view('profile.address-create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        auth()->user()->addresses()->create($request->all());

        return redirect()->route('address.index')
            ->with('success', 'Address added successfully!');
    }

    public function edit(Address $address)
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        return view('profile.address-edit', compact('address'));
    }

    public function update(Request $request, Address $address)
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'recipient_name' => 'required|string|max:255',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'is_default' => 'boolean',
        ]);

        $address->update($request->all());

        return redirect()->route('address.index')
            ->with('success', 'Address updated successfully!');
    }

    public function destroy(Address $address)
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Address deleted successfully!');
    }

    public function setDefault(Address $address)
    {
        // Check ownership
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $address->update(['is_default' => true]);

        return back()->with('success', 'Default address updated!');
    }
}