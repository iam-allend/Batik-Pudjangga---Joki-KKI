<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist
     */
    public function index()
    {
        $wishlistItems = Auth::user()->wishlists()
            ->with('product')
            ->latest()
            ->paginate(12);

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add product to wishlist - SIMPLE POST (No AJAX)
     */
    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $productId = $request->product_id;
            $userId = Auth::id();

            // Check if already in wishlist
            $exists = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists();

            if ($exists) {
                return redirect()->back()->with('info', 'Product already in your wishlist!');
            }

            // Add to wishlist
            Wishlist::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);

            return redirect()->back()->with('success', 'Product added to wishlist! ❤️');

        } catch (\Exception $e) {
            Log::error('Wishlist Add Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to add to wishlist!');
        }
    }

    /**
     * Remove product from wishlist - SIMPLE POST (No AJAX)
     */
    public function remove(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $productId = $request->product_id;
            $userId = Auth::id();

            // Remove from wishlist
            $deleted = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();

            if ($deleted) {
                return redirect()->back()->with('success', 'Product removed from wishlist!');
            }

            return redirect()->back()->with('info', 'Product not in wishlist!');

        } catch (\Exception $e) {
            Log::error('Wishlist Remove Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove from wishlist!');
        }
    }

    /**
     * Toggle wishlist - SIMPLE POST (No AJAX)
     * Add if not exists, Remove if exists
     */
    public function toggle(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id'
            ]);

            $productId = $request->product_id;
            $userId = Auth::id();

            // Check if exists
            $wishlist = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($wishlist) {
                // Remove
                $wishlist->delete();
                return redirect()->back()->with('success', 'Product removed from wishlist!');
            } else {
                // Add
                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);
                return redirect()->back()->with('success', 'Product added to wishlist! ❤️');
            }

        } catch (\Exception $e) {
            Log::error('Wishlist Toggle Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update wishlist!');
        }
    }
}