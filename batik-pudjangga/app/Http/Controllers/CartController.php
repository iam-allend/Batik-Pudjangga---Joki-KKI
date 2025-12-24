<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = auth()->user()->carts()
            ->with('product')
            ->get();

        $subtotal = $cartItems->sum('subtotal');

        return view('cart.index', compact('cartItems', 'subtotal'));
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
            return back()->with('error', 'Insufficient stock available.');
        }

        // Check if item already in cart
        $cartItem = Cart::where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->where('size', $request->size)
            ->first();

        if ($cartItem) {
            // Update quantity
            $newQuantity = $cartItem->quantity + $request->quantity;
            
            if ($product->stock < $newQuantity) {
                return back()->with('error', 'Insufficient stock available.');
            }

            $cartItem->update([
                'quantity' => $newQuantity,
                'notes' => $request->notes ?? $cartItem->notes,
            ]);
        } else {
            // Create new cart item
            Cart::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->current_price,
                'size' => $request->size,
                'notes' => $request->notes,
            ]);
        }

        return back()->with('success', 'Product added to cart successfully!');
    }

    public function update(Request $request, Cart $cart)
    {
        // Check ownership
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Check stock
        if ($cart->product->stock < $request->quantity) {
            return back()->with('error', 'Insufficient stock available.');
        }

        $cart->update([
            'quantity' => $request->quantity,
        ]);

        return back()->with('success', 'Cart updated successfully!');
    }

    public function remove(Cart $cart)
    {
        // Check ownership
        if ($cart->user_id !== auth()->id()) {
            abort(403);
        }

        $cart->delete();

        return back()->with('success', 'Item removed from cart!');
    }

    public function checkoutSelected(Request $request)
    {
        $request->validate([
            'selected_items' => 'required|array',
            'selected_items.*' => 'exists:carts,id',
        ]);

        // Store selected items in session
        session(['checkout_items' => $request->selected_items]);

        return redirect()->route('checkout.index');
    }
}
