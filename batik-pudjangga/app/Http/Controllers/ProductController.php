<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function show(Product $product)
    {
        $product->load(['images', 'variants']);

        // Related products (same category)
        $relatedProducts = Product::with('images')
            ->where('category', $product->category)
            ->where('id', '!=', $product->id)
            ->inStock()
            ->take(4)
            ->get();

        // Check if in wishlist
        $inWishlist = false;
        if (auth()->check()) {
            $inWishlist = auth()->user()->wishlists()
                ->where('product_id', $product->id)
                ->exists();
        }

        return view('products.show', compact('product', 'relatedProducts', 'inWishlist'));
    }
}
