@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stats-label">Total Products</p>
                        <h2 class="stats-number">{{ $totalProducts }}</h2>
                    </div>
                    <div style="font-size: 2rem; color: var(--primary-color);">
                        <i class="fas fa-box"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stats-label">Total Orders</p>
                        <h2 class="stats-number">{{ $totalOrders }}</h2>
                    </div>
                    <div style="font-size: 2rem; color: #28a745;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stats-label">Total Users</p>
                        <h2 class="stats-number">{{ $totalUsers }}</h2>
                    </div>
                    <div style="font-size: 2rem; color: #007bff;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="stats-label">Total Revenue</p>
                        <h2 class="stats-number">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h2>
                    </div>
                    <div style="font-size: 2rem; color: #ffc107;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Cards -->
<div class="row">
    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pending Orders</h6>
                <h3 class="mb-0" style="color: #ffc107;">{{ $pendingOrders }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #17a2b8;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Processing Orders</h6>
                <h3 class="mb-0" style="color: #17a2b8;">{{ $processingOrders }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #007bff;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Shipped Orders</h6>
                <h3 class="mb-0" style="color: #007bff;">{{ $shippedOrders }}</h3>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Completed Orders</h6>
                <h3 class="mb-0" style="color: #28a745;">{{ $completedOrders }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Recent Orders -->
<div class="row">
    <!-- Monthly Revenue Chart -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Monthly Revenue
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Low Stock Products -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Products
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                @forelse($lowStockProducts as $product)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid #eee;">
                        <div>
                            <strong>{{ $product->name }}</strong>
                            <br>
                            <small class="text-muted">Stock: 
                                <span class="badge bg-danger">{{ $product->stock }}</span>
                            </small>
                        </div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                @empty
                    <p class="text-muted text-center">No low stock products</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-clock me-2"></i>Recent Orders</span>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order Code</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr>
                                    <td><strong>{{ $order->order_code }}</strong></td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'completed' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No recent orders</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Monthly Revenue Chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const monthlyData = @json($monthlyRevenue);
    
    // Prepare data for all 12 months
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const revenueData = new Array(12).fill(0);
    
    monthlyData.forEach(item => {
        revenueData[item.month - 1] = item.total;
    });
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Revenue (Rp)',
                data: revenueData,
                borderColor: '#8B4513',
                backgroundColor: 'rgba(139, 69, 19, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush