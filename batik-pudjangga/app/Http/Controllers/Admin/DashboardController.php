<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistics
        $totalProducts = Product::count();
        $totalOrders = Order::count();
        $totalUsers = User::where('is_admin', false)->count();
        $totalRevenue = Order::whereIn('status', ['completed', 'shipped'])->sum('total_amount');

        // Recent orders
        $recentOrders = Order::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Orders by status
        $pendingOrders = Order::pending()->count();
        $processingOrders = Order::processing()->count();
        $shippedOrders = Order::status('shipped')->count();
        $completedOrders = Order::status('completed')->count();

        // Low stock products
        $lowStockProducts = Product::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->orderBy('stock', 'asc')
            ->take(10)
            ->get();

        // Monthly revenue chart data
        $monthlyRevenue = Order::whereIn('status', ['completed', 'shipped'])
            ->whereYear('created_at', now()->year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'totalUsers',
            'totalRevenue',
            'recentOrders',
            'pendingOrders',
            'processingOrders',
            'shippedOrders',
            'completedOrders',
            'lowStockProducts',
            'monthlyRevenue'
        ));
    }

    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $orders = Order::with('items.product')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereIn('status', ['completed', 'shipped'])
            ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        return view('admin.reports.sales', compact(
            'orders',
            'totalSales',
            'totalOrders',
            'averageOrderValue',
            'startDate',
            'endDate'
        ));
    }

    public function productsReport()
    {
        $products = Product::withCount(['orderItems'])
            ->orderBy('order_items_count', 'desc')
            ->get();

        return view('admin.reports.products', compact('products'));
    }
}
