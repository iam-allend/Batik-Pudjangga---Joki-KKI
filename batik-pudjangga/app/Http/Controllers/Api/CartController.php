<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->carts()
            ->with('product')
            ->get();

        $subtotal = $cartItems->sum('subtotal');

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'count' => $cartItems->count(),
            ],
        ]);
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'size' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check stock
        if ($product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available.',
            ], 400);
        }

        // Check if item already in cart
        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('size', $request->size)
            ->first();

        if ($cartItem) {
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            if ($product->stock < $newQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available.',
                ], 400);
            }

            $cartItem->update([
                'quantity' => $newQuantity,
                'notes' => $request->notes ?? $cartItem->notes,
            ]);
        } else {
            $cartItem = Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->current_price,
                'size' => $request->size,
                'notes' => $request->notes,
            ]);
        }

        // Get updated cart count
        $cartCount = auth()->user()->carts()->count();

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully!',
            'data' => [
                'cart_count' => $cartCount,
                'item' => $cartItem->load('product'),
            ],
        ]);
    }

    public function update(Request $request, Cart $cart)
    {
        // Check ownership
        if ($cart->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check stock
        if ($cart->product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available.',
            ], 400);
        }

        $cart->update(['quantity' => $request->quantity]);

        // Get updated subtotal
        $cartItems = auth()->user()->carts;
        $subtotal = $cartItems->sum('subtotal');

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully!',
            'data' => [
                'item' => $cart->fresh(),
                'subtotal' => $subtotal,
            ],
        ]);
    }

    public function remove(Cart $cart)
    {
        // Check ownership
        if ($cart->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $cart->delete();

        // Get updated cart data
        $cartItems = auth()->user()->carts;
        $subtotal = $cartItems->sum('subtotal');
        $cartCount = $cartItems->count();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart!',
            'data' => [
                'cart_count' => $cartCount,
                'subtotal' => $subtotal,
            ],
        ]);
    }

    public function count()
    {
        $count = auth()->user()->carts()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count,
            ],
        ]);
    }

    // Wishlist toggle
    public function toggleWishlist(Product $product)
    {
        $wishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->first();

        if ($wishlist) {
            $wishlist->delete();
            $inWishlist = false;
            $message = 'Product removed from wishlist!';
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ]);
            $inWishlist = true;
            $message = 'Product added to wishlist!';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'in_wishlist' => $inWishlist,
            ],
        ]);
    }

    public function checkWishlist(Product $product)
    {
        $inWishlist = Wishlist::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->exists();

        return response()->json([
            'success' => true,
            'data' => [
                'in_wishlist' => $inWishlist,
            ],
        ]);
    }
}
