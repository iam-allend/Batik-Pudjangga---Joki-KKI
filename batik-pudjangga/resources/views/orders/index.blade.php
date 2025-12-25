@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            @include('profile.partials.sidebar')
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Orders</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Filter Tabs -->
                    <ul class="nav nav-pills mb-4">
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == '' ? 'active' : '' }}" href="{{ route('orders.index') }}">
                                All Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'pending' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'pending']) }}">
                                Pending
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'processing' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'processing']) }}">
                                Processing
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'shipped' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'shipped']) }}">
                                Shipped
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'completed' ? 'active' : '' }}" href="{{ route('orders.index', ['status' => 'completed']) }}">
                                Completed
                            </a>
                        </li>
                    </ul>

                    @if($orders->count() > 0)
                        @foreach($orders as $order)
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1">Order #{{ $order->order_code }}</h6>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $order->created_at->format('d M Y, H:i') }}
                                                </small>
                                            </div>
                                            <span class="badge bg-{{ $order->status_color }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </div>
                                        
                                        <p class="mb-1 text-muted">
                                            <i class="fas fa-box me-1"></i> {{ $order->items->count() }} item(s)
                                        </p>
                                        
                                        @if($order->resi_code)
                                        <p class="mb-0">
                                            <i class="fas fa-truck me-1"></i> Tracking: <strong>{{ $order->resi_code }}</strong>
                                        </p>
                                        @endif
                                    </div>
                                    
                                    <div class="col-md-4 text-md-end">
                                        <h5 class="mb-3">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h5>
                                        <div class="d-flex gap-2 justify-content-md-end">
                                            <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> View Detail
                                            </a>
                                            @if($order->status == 'pending')
                                            <form action="{{ route('orders.cancel', $order) }}" method="POST" 
                                                  onsubmit="return confirm('Cancel this order?')">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No orders found.</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-primary">Start Shopping</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
