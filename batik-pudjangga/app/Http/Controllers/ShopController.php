<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('images')->inStock();

        // Filtering
        if ($request->has('category') && $request->category) {
            $query->category($request->category);
        }

        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                    $query->latest();
                    break;
                case 'name':
                    $query->orderBy('name', 'asc');
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(12);

        return view('shop.index', compact('products'));
    }

    public function men()
    {
        $products = Product::with('images')
            ->category('men')
            ->inStock()
            ->paginate(12);

        return view('shop.men', compact('products'));
    }

    public function women()
    {
        $products = Product::with('images')
            ->category('women')
            ->inStock()
            ->paginate(12);

        return view('shop.women', compact('products'));
    }

    public function pants()
    {
        $products = Product::with('images')
            ->category('pants')
            ->inStock()
            ->paginate(12);

        return view('shop.pants', compact('products'));
    }

    public function oneset()
    {
        $products = Product::with('images')
            ->category('oneset')
            ->inStock()
            ->paginate(12);

        return view('shop.oneset', compact('products'));
    }

    public function newCollection()
    {
        $products = Product::with('images')
            ->new()
            ->inStock()
            ->latest()
            ->paginate(12);

        return view('shop.new-collection', compact('products'));
    }

    public function sale()
    {
        $products = Product::with('images')
            ->onSale()
            ->inStock()
            ->paginate(12);

        return view('shop.sale', compact('products'));
    }

    public function search(Request $request)
    {
        $keyword = $request->input('q');
        
        $products = Product::with('images')
            ->where(function($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                      ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->inStock()
            ->paginate(12);

        return view('shop.search', compact('products', 'keyword'));
    }
}