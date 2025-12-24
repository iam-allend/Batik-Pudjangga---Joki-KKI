<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Subscriber;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $newProducts = Product::with('images')
            ->new()
            ->inStock()
            ->latest()
            ->take(8)
            ->get();

        $saleProducts = Product::with('images')
            ->onSale()
            ->inStock()
            ->take(8)
            ->get();

        $featuredProducts = Product::with('images')
            ->inStock()
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

        return view('home.index', compact('newProducts', 'saleProducts', 'featuredProducts'));
    }

    public function about()
    {
        return view('home.about');
    }

    public function shippingInfo()
    {
        return view('home.shipping-info');
    }

    public function returnPolicy()
    {
        return view('home.return-policy');
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscribers,email',
        ]);

        Subscriber::create([
            'email' => $request->email,
            'is_active' => true,
        ]);

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }
}
