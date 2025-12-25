<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        // Get selected items from session
        $selectedItems = session('checkout_items', []);

        if (empty($selectedItems)) {
            return redirect()->route('cart.index')->with('error', 'No items selected for checkout!');
        }

        // Get cart items
        $cartItems = Cart::whereIn('id', $selectedItems)
            ->where('user_id', Auth::id())
            ->with('product')
            ->get();

        // Check stock availability
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return redirect()->route('cart.index')
                    ->with('error', "Product {$item->product->name} is out of stock!");
            }
        }

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        // Get user addresses
        $addresses = Auth::user()->addresses;

        return view('checkout.index', compact('cartItems', 'subtotal', 'addresses', 'selectedItems'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'shipping_method' => ['required', 'in:regular,express'],
            'payment_method' => ['required', 'in:transfer,cod'],
            'shipping_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $selectedItems = explode(',', $request->selected_items);
        $cartItems = Cart::whereIn('id', $selectedItems)
            ->where('user_id', Auth::id())
            ->with('product')
            ->get();

        // Calculate subtotal
        $subtotal = $cartItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });

        $shippingCost = $request->shipping_cost;
        $totalAmount = $subtotal + $shippingCost;

        // Get address
        $address = Address::findOrFail($request->address_id);

        DB::beginTransaction();
        try {
            // Create order
            $order = Order::create([
                'order_code' => Order::generateOrderCode(),
                'user_id' => Auth::id(),
                'recipient_name' => $address->recipient_name,
                'address' => $address->address,
                'city' => $address->city,
                'province' => $address->province,
                'postal_code' => $address->postal_code,
                'phone' => $address->phone,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'shipping_method' => ucfirst($request->shipping_method) . ' Shipping',
                'status' => 'pending',
            ]);

            // Create order items & reduce stock
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'size' => $item->size,
                    'notes' => $item->notes,
                ]);

                // Reduce stock
                $item->product->decrement('stock', $item->quantity);

                // Remove from cart
                $item->delete();
            }

            DB::commit();

            // Clear session
            session()->forget('checkout_items');

            return redirect()->route('checkout.payment', $order);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong! Please try again.');
        }
    }

    public function payment(Order $order)
    {
        // Authorize
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return view('checkout.payment', compact('order'));
    }
}
