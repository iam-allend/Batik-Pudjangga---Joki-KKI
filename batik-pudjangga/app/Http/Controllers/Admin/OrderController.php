<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->status($request->status);
        }

        // Search by order code
        if ($request->has('search') && $request->search) {
            $query->where('order_code', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'design_status' => 'nullable|in:pending,confirmed,rejected',
        ]);

        $order->update($request->only('status', 'design_status'));

        return back()->with('success', 'Order updated successfully!');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        // If cancelling, restore stock
        if ($request->status === 'cancelled' && $order->status !== 'cancelled') {
            foreach ($order->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        $order->update(['status' => $request->status]);

        return back()->with('success', 'Order status updated successfully!');
    }

    public function addResi(Request $request, Order $order)
    {
        $request->validate([
            'resi_code' => 'required|string|max:50',
        ]);

        $order->update([
            'resi_code' => $request->resi_code,
            'status' => 'shipped',
        ]);

        return back()->with('success', 'Resi code added and order status updated to shipped!');
    }
}
