<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get selected cart items from session or all cart items
        $selectedIds = session('checkout_items');
        
        if ($selectedIds) {
            $cartItems = Cart::with('product')
                ->whereIn('id', $selectedIds)
                ->where('user_id', $user->id)
                ->get();
        } else {
            $cartItems = $user->carts()->with('product')->get();
        }

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty!');
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum('subtotal');

        // Get user addresses
        $addresses = $user->addresses;
        $defaultAddress = $user->defaultAddress;

        // Get provinces for shipping - FIXED
        $provinces = ShippingZone::select('province', 'zone')
            ->distinct()
            ->orderBy('province')
            ->get();

        return view('checkout.index', compact('cartItems', 'subtotal', 'addresses', 'defaultAddress', 'provinces'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'recipient_name' => 'required|string|max:100',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'postal_code' => 'required|string|max:10',
            'phone' => 'required|string|max:20',
            'payment_method' => 'required|in:transfer,cod',
            'shipping_method' => 'required|in:regular,express',
        ]);

        $user = auth()->user();

        // Get cart items
        $selectedIds = session('checkout_items');
        if ($selectedIds) {
            $cartItems = Cart::with('product')
                ->whereIn('id', $selectedIds)
                ->where('user_id', $user->id)
                ->get();
        } else {
            $cartItems = $user->carts()->with('product')->get();
        }

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')
                ->with('error', 'Your cart is empty!');
        }

        // Calculate amounts
        $subtotal = $cartItems->sum('subtotal');
        $shippingCost = ShippingZone::getCost($request->province, $request->shipping_method);
        $totalAmount = $subtotal + $shippingCost;

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'recipient_name' => $request->recipient_name,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'postal_code' => $request->postal_code,
                'phone' => $request->phone,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'shipping_method' => $request->shipping_method,
                'status' => 'pending',
                'design_status' => 'pending',
            ]);

            // Create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->price,
                    'size' => $cartItem->size,
                    'notes' => $cartItem->notes,
                ]);

                // Update product stock
                $product = $cartItem->product;
                $product->decrement('stock', $cartItem->quantity);
            }

            // Delete cart items
            Cart::whereIn('id', $cartItems->pluck('id'))->delete();

            // Clear session
            session()->forget('checkout_items');

            DB::commit();

            // Redirect based on payment method
            if ($request->payment_method === 'transfer') {
                return redirect()->route('checkout.payment', $order)
                    ->with('success', 'Order placed successfully! Please complete your payment.');
            } else {
                return redirect()->route('checkout.success', $order)
                    ->with('success', 'Order placed successfully! Your order will be processed soon.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to process order. Please try again.');
        }
    }

    public function payment(Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if already paid or not transfer
        if ($order->payment_method !== 'transfer' || $order->status !== 'pending') {
            return redirect()->route('orders.show', $order);
        }

        return view('checkout.payment', compact('order'));
    }

    public function confirmPayment(Request $request, Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // In real app, you would handle payment proof upload here
        // For now, just update status
        $order->update([
            'status' => 'processing',
        ]);

        return redirect()->route('checkout.success', $order)
            ->with('success', 'Payment confirmation received! Your order is being processed.');
    }

    public function success(Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }
}
