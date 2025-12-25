<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = auth()->user()->wishlists()
            ->with('product.images')
            ->latest()
            ->get();

        return view('wishlist.index', compact('wishlistItems'));
    }

    public function add(Product $product)
    {
        $exists = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return back()->with('info', 'Product already in wishlist!');
        }

        Wishlist::create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Product added to wishlist!');
    }

    public function remove(Product $product)
    {
        Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Product removed from wishlist!');
    }
    
    // SIMPLE TOGGLE - untuk AJAX
    public function toggle(Product $product)
    {
        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            return response()->json([
                'success' => true,
                'in_wishlist' => false,
                'message' => 'Removed from wishlist'
            ]);
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
            return response()->json([
                'success' => true,
                'in_wishlist' => true,
                'message' => 'Added to wishlist'
            ]);
        }
    }
}
