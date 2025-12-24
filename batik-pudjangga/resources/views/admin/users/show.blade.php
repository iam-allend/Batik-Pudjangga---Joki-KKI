@extends('layouts.admin')

@section('title', 'User Details')
@section('page-title', 'User Details - ' . $user->name)

@section('content')
<div class="row">
    <!-- User Information -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <div class="user-avatar mx-auto mb-3" 
                     style="width: 100px; height: 100px; font-size: 2.5rem;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h4>{{ $user->name }}</h4>
                <p class="text-muted">{{ $user->email }}</p>
                <span class="badge bg-{{ $user->is_admin ? 'danger' : 'primary' }}">
                    {{ $user->is_admin ? 'Administrator' : 'Customer' }}
                </span>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Information
            </div>
            <div class="card-body">
                <p><strong>Phone:</strong> {{ $user->phone ?? 'Not set' }}</p>
                <p><strong>City:</strong> {{ $user->city ?? 'Not set' }}</p>
                <p><strong>Postal Code:</strong> {{ $user->postal_code ?? 'Not set' }}</p>
                <p><strong>Joined:</strong> {{ $user->created_at->format('d M Y') }}</p>
                <p class="mb-0"><strong>Address:</strong><br>
                    {{ $user->address ?? 'Not set' }}
                </p>
            </div>
        </div>
    </div>
    
    <!-- User Activity -->
    <div class="col-md-8">
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-primary">{{ $user->orders->count() }}</h3>
                        <p class="mb-0 text-muted">Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-success">Rp {{ number_format($user->orders->sum('total_amount'), 0, ',', '.') }}</h3>
                        <p class="mb-0 text-muted">Total Spent</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="text-info">{{ $user->addresses->count() }}</h3>
                        <p class="mb-0 text-muted">Saved Addresses</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-shopping-cart me-2"></i>Recent Orders
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order Code</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->orders()->latest()->take(10)->get() as $order)
                                <tr>
                                    <td><strong>{{ $order->order_code }}</strong></td>
                                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'][$order->status] ?? 'secondary' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('d M Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No orders yet</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Addresses -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-2"></i>Saved Addresses
            </div>
            <div class="card-body">
                @forelse($user->addresses as $address)
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $address->recipient_name }}</strong>
                                @if($address->is_default)
                                    <span class="badge bg-success ms-2">Default</span>
                                @endif
                                <p class="mb-1 mt-2">{{ $address->address }}</p>
                                <p class="mb-1">{{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</p>
                                <p class="mb-0 text-muted">{{ $address->phone }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center mb-0">No saved addresses</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
