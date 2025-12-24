<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->orders()->with('items.product')->latest();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->status($request->status);
        }

        $orders = $query->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.product');

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order)
    {
        // Check ownership
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        // Only allow cancellation if order is still pending
        if ($order->status !== 'pending') {
            return back()->with('error', 'This order cannot be cancelled.');
        }

        // Restore product stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        // Update order status
        $order->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Order cancelled successfully. Stock has been restored.');
    }
}

