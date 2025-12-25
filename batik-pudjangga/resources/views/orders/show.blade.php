@extends('layouts.app')

@section('title', 'Order Detail - #' . $order->order_code)

@section('content')
<div class="container py-5">
    <div class="row">
        <!-- Back Button -->
        <div class="col-12 mb-3">
            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>

        <!-- Order Status Timeline -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Order #{{ $order->order_code }}</h5>
                        <span class="badge bg-{{ $order->status_color }} fs-6">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>

                    <!-- Status Timeline -->
                    <div class="order-timeline">
                        <div class="timeline-item {{ in_array($order->status, ['pending', 'processing', 'shipped', 'completed']) ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Order Placed</h6>
                                <small>{{ $order->created_at->format('d M Y, H:i') }}</small>
                            </div>
                        </div>
                        
                        <div class="timeline-item {{ in_array($order->status, ['processing', 'shipped', 'completed']) ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Processing</h6>
                                @if($order->status != 'pending')
                                    <small>Order is being prepared</small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="timeline-item {{ in_array($order->status, ['shipped', 'completed']) ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Shipped</h6>
                                @if($order->resi_code)
                                    <small>Tracking: <strong>{{ $order->resi_code }}</strong></small>
                                @endif
                            </div>
                        </div>
                        
                        <div class="timeline-item {{ $order->status == 'completed' ? 'completed' : '' }}">
                            <div class="timeline-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="timeline-content">
                                <h6>Completed</h6>
                                @if($order->status == 'completed')
                                    <small>{{ $order->updated_at->format('d M Y, H:i') }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
                </div>
                <div class="card-body">
                    @foreach($order->items as $item)
                    <div class="d-flex mb-3 pb-3 border-bottom">
                        <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                             alt="{{ $item->product->name }}"
                             class="img-thumbnail me-3"
                             style="width: 100px; height: 100px; object-fit: cover;">
                        <div class="flex-grow-1">
                            <h6>{{ $item->product->name }}</h6>
                            <p class="text-muted mb-1">
                                @if($item->size)
                                    Size: {{ $item->size }} | 
                                @endif
                                Qty: {{ $item->quantity }}
                            </p>
                            @if($item->notes)
                                <p class="text-muted mb-1">
                                    <small><i>Note: {{ $item->notes }}</i></small>
                                </p>
                            @endif
                            <p class="mb-0">
                                <strong>Rp {{ number_format($item->price, 0, ',', '.') }}</strong>
                                <span class="text-muted"> x {{ $item->quantity }}</span>
                            </p>
                        </div>
                        <div class="text-end">
                            <strong>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Order Summary & Shipping -->
        <div class="col-lg-4">
            <!-- Shipping Address -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Shipping Address</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $order->recipient_name }}</strong></p>
                    <p class="mb-1">{{ $order->phone }}</p>
                    <p class="mb-0 text-muted">
                        {{ $order->address }}<br>
                        {{ $order->city }}, {{ $order->province }}<br>
                        {{ $order->postal_code }}
                    </p>
                </div>
            </div>

            <!-- Payment & Shipping Info -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Payment & Shipping</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payment Method:</span>
                        <strong class="text-uppercase">{{ $order->payment_method }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Shipping Method:</span>
                        <strong>{{ $order->shipping_method }}</strong>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Cost:</span>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total:</strong>
                        <strong class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($order->status == 'pending')
            <div class="card mt-3">
                <div class="card-body text-center">
                    <p class="mb-3">Need to cancel this order?</p>
                    <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                        @csrf
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-times"></i> Cancel Order
                        </button>
                    </form>
                </div>
            </div>
            @endif

            @if($order->status == 'shipped' && $order->resi_code)
            <div class="card mt-3">
                <div class="card-body text-center">
                    <p class="mb-3">Track your package:</p>
                    <div class="input-group">
                        <input type="text" class="form-control" value="{{ $order->resi_code }}" readonly>
                        <button class="btn btn-primary" onclick="copyResi()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.order-timeline {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-top: 30px;
}

.order-timeline::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 3px;
    background: #e0e0e0;
    z-index: 0;
}

.timeline-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 1;
}

.timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    border: 3px solid #fff;
}

.timeline-item.completed .timeline-icon {
    background: #28a745;
    color: white;
}

.timeline-content h6 {
    font-size: 14px;
    margin-bottom: 5px;
}

.timeline-content small {
    font-size: 12px;
    color: #666;
}
</style>

<script>
function copyResi() {
    const resiInput = document.querySelector('input[readonly]');
    resiInput.select();
    document.execCommand('copy');
    alert('Tracking number copied!');
}
</script>
@endsection