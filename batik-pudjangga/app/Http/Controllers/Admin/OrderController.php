<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display all orders with filters
     */
    public function index(Request $request)
    {
        $query = Order::with('user')->latest();

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->status($request->status);
        }

        // Search by order code or customer name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(20);

        // Get status counts for filter badges
        $statusCounts = [
            'all' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'shipped' => Order::where('status', 'shipped')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order (general)
     */
    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            'design_status' => 'nullable|in:pending,confirmed,rejected',
        ]);

        $order->update($request->only('status', 'design_status'));

        return back()->with('success', 'Order updated successfully!');
    }

    /**
     * Update order status specifically
     */
    public function updateStatus(Request $request, Order $order)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,shipped,completed,cancelled',
            ]);

            $oldStatus = $order->status;
            $newStatus = $request->status;

            // If cancelling order, restore product stock
            if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
                foreach ($order->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
                
                Log::info("Order {$order->order_code} cancelled, stock restored");
            }

            // If un-cancelling order (from cancelled to any other status)
            if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
                // Check if stock is available
                foreach ($order->items as $item) {
                    if ($item->product->stock < $item->quantity) {
                        return back()->with('error', "Insufficient stock for {$item->product->name}!");
                    }
                }
                
                // Deduct stock again
                foreach ($order->items as $item) {
                    $item->product->decrement('stock', $item->quantity);
                }
                
                Log::info("Order {$order->order_code} reactivated, stock deducted");
            }

            // Update status
            $order->update(['status' => $newStatus]);

            return back()->with('success', 'Order status updated successfully!');

        } catch (\Exception $e) {
            Log::error('Update Order Status Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to update order status!');
        }
    }

    /**
     * Add tracking/resi code
     */
    public function addResi(Request $request, Order $order)
    {
        try {
            $request->validate([
                'resi_code' => 'required|string|max:50',
            ]);

            // Update resi code and automatically set status to shipped
            $order->update([
                'resi_code' => $request->resi_code,
                'status' => 'shipped',
            ]);

            Log::info("Resi code {$request->resi_code} added to order {$order->order_code}");

            return back()->with('success', 'Resi code added and order status updated to shipped!');

        } catch (\Exception $e) {
            Log::error('Add Resi Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to add resi code!');
        }
    }

    /**
     * Export orders to CSV (optional)
     */
    public function export(Request $request)
    {
        // TODO: Implement CSV export
    }

    /**
     * Bulk update orders (optional)
     */
    public function bulkUpdate(Request $request)
    {
        // TODO: Implement bulk actions
    }
}