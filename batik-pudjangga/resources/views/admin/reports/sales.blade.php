@extends('layouts.admin')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('content')
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-filter me-2"></i>Filter Report
    </div>
    <div class="card-body">
        <form action="{{ route('admin.reports.sales') }}" method="GET">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" 
                           value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" 
                           value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Generate Report
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card" style="border-left: 4px solid #28a745;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Sales</h6>
                <h3 class="mb-0 text-success">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="border-left: 4px solid #007bff;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Orders</h6>
                <h3 class="mb-0 text-primary">{{ $totalOrders }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <h6 class="text-muted mb-2">Average Order Value</h6>
                <h3 class="mb-0 text-warning">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-2"></i>Sales Details</span>
        <button onclick="printReport()" class="btn btn-sm btn-success">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Subtotal</th>
                        <th>Shipping</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_code }}</strong></td>
                            <td>{{ $order->user->name }}</td>
                            <td>{{ $order->items->count() }}</td>
                            <td>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $order->payment_method == 'transfer' ? 'primary' : 'success' }}">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('d M Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-active">
                        <th colspan="5" class="text-end">TOTAL:</th>
                        <th>Rp {{ number_format($totalSales, 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printReport() {
    window.print();
}
</script>
@endpush
