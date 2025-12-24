@extends('layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Orders Management')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-shopping-cart me-2"></i>All Orders
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form action="{{ route('admin.orders.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search order code..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <form action="{{ route('admin.orders.index') }}" method="GET">
                    <select class="form-select" name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="shipped" {{ request('status') == 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Order Code</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_code }}</strong></td>
                            <td>
                                {{ $order->user->name }}
                                <br>
                                <small class="text-muted">{{ $order->user->email }}</small>
                            </td>
                            <td>{{ $order->items->count() }} item(s)</td>
                            <td><strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                            <td>
                                <span class="badge bg-{{ $order->payment_method == 'transfer' ? 'primary' : 'success' }}">
                                    {{ strtoupper($order->payment_method) }}
                                </span>
                            </td>
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
                            <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
