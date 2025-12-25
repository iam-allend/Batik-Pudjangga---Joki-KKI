<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Auth::user()->carts()->with('product')->get();
        return view('cart.index', compact('cartItems'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check stock
        if ($product->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Not enough stock available!');
        }

        // Get current price (check if on sale)
        $price = $product->is_sale && $product->sale_price 
            ? $product->sale_price 
            : $product->price;

        // Check if item already exists in cart
        $existingCart = Cart::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->where('size', $request->size)
            ->first();

        if ($existingCart) {
            // Update quantity
            $newQuantity = $existingCart->quantity + $request->quantity;
            
            if ($newQuantity > $product->stock) {
                return redirect()->back()->with('error', 'Cannot add more than available stock!');
            }

            $existingCart->update([
                'quantity' => $newQuantity,
                'notes' => $request->notes ?? $existingCart->notes
            ]);
        } else {
            // Create new cart item
            Cart::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $price,
                'size' => $request->size,
                'notes' => $request->notes,
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Product added to cart!');
    }

    public function update(Request $request, Cart $cart)
    {
        // Authorize
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }

        $action = $request->input('action');

        if ($action === 'increase') {
            if ($cart->quantity < $cart->product->stock) {
                $cart->increment('quantity');
            }
        } elseif ($action === 'decrease') {
            if ($cart->quantity > 1) {
                $cart->decrement('quantity');
            }
        }

        return redirect()->route('cart.index');
    }

    public function remove(Cart $cart)
    {
        // Authorize
        if ($cart->user_id !== Auth::id()) {
            abort(403);
        }

        $cart->delete();

        return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
    }

    public function checkoutSelected(Request $request)
    {
        $request->validate([
            'selected_items' => ['required', 'string']
        ]);

        $selectedIds = explode(',', $request->selected_items);

        // Validate that all items belong to user
        $carts = Cart::whereIn('id', $selectedIds)
            ->where('user_id', Auth::id())
            ->get();

        if ($carts->count() != count($selectedIds)) {
            return redirect()->route('cart.index')->with('error', 'Invalid items selected!');
        }

        // Store selected items in session
        session(['checkout_items' => $selectedIds]);

        return redirect()->route('checkout.index');
    }
}
