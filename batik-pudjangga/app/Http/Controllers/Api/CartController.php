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
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'nullable|integer|min:1',
                'size' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            $product = Product::findOrFail($request->product_id);
            $quantity = $request->quantity ?? 1;

            // Check stock
            if ($product->stock < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available. Only ' . $product->stock . ' items left.',
                ], 400);
            }

            // Check if item already in cart with same size
            $cartItem = Cart::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->where('size', $request->size)
                ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                
                if ($product->stock < $newQuantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot add more. Only ' . $product->stock . ' items available.',
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
                    'quantity' => $quantity,
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, Cart $cart)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cart: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function remove(Cart $cart)
    {
        try {
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function count()
    {
        try {
            $count = auth()->user()->carts()->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'count' => $count,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cart count: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Wishlist toggle
    public function toggleWishlist(Product $product)
    {
        try {
            $wishlist = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->first();

            if ($wishlist) {
                $wishlist->delete();
                $inWishlist = false;
                $message = 'Removed from wishlist!';
            } else {
                Wishlist::create([
                    'user_id' => auth()->id(),
                    'product_id' => $product->id,
                ]);
                $inWishlist = true;
                $message = 'Added to wishlist!';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'in_wishlist' => $inWishlist,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update wishlist: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function checkWishlist(Product $product)
    {
        try {
            $inWishlist = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->exists();

            return response()->json([
                'success' => true,
                'data' => [
                    'in_wishlist' => $inWishlist,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist: ' . $e->getMessage(),
            ], 500);
        }
    }
}

