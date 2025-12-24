@extends('layouts.admin')

@section('title', 'Order Details')
@section('page-title', 'Order Details - ' . $order->order_code)

@section('content')
<div class="row">
    <!-- Order Information -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-info-circle me-2"></i>Order Information</span>
                <span class="badge bg-{{ ['pending' => 'warning', 'processing' => 'info', 'shipped' => 'primary', 'completed' => 'success', 'cancelled' => 'danger'][$order->status] ?? 'secondary' }}">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Order Code:</strong> {{ $order->order_code }}</p>
                        <p><strong>Order Date:</strong> {{ $order->created_at->format('d M Y H:i') }}</p>
                        <p><strong>Payment Method:</strong> 
                            <span class="badge bg-{{ $order->payment_method == 'transfer' ? 'primary' : 'success' }}">
                                {{ strtoupper($order->payment_method) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Customer:</strong> {{ $order->user->name }}</p>
                        <p><strong>Email:</strong> {{ $order->user->email }}</p>
                        <p><strong>Phone:</strong> {{ $order->phone }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order Items -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-box me-2"></i>Order Items
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Size</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                                 alt="{{ $item->product->name }}"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                            <div>
                                                <strong>{{ $item->product->name }}</strong>
                                                @if($item->notes)
                                                    <br><small class="text-muted">Note: {{ $item->notes }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $item->size ?? '-' }}</td>
                                    <td>Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td><strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Subtotal:</strong></td>
                                <td><strong>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end">
                                    <strong>Shipping ({{ ucfirst($order->shipping_method) }}):</strong>
                                </td>
                                <td><strong>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</strong></td>
                            </tr>
                            <tr class="table-active">
                                <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                                <td><strong class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Shipping Information -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-truck me-2"></i>Shipping Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Recipient:</strong> {{ $order->recipient_name }}</p>
                        <p><strong>Phone:</strong> {{ $order->phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Shipping Method:</strong> {{ ucfirst($order->shipping_method) }}</p>
                        @if($order->resi_code)
                            <p><strong>Resi Code:</strong> <span class="badge bg-success">{{ $order->resi_code }}</span></p>
                        @endif
                    </div>
                </div>
                <p class="mb-0"><strong>Address:</strong><br>
                    {{ $order->address }}<br>
                    {{ $order->city }}, {{ $order->province }} {{ $order->postal_code }}
                </p>
            </div>
        </div>
    </div>
    
    <!-- Actions & Status -->
    <div class="col-md-4">
        <!-- Update Status -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Update Order Status
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-check me-1"></i>Update Status
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Add Resi Code -->
        @if($order->status == 'processing' || $order->status == 'shipped')
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-barcode me-2"></i>Tracking Number
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.orders.add-resi', $order) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Resi Code</label>
                            <input type="text" class="form-control" name="resi_code" 
                                   value="{{ $order->resi_code }}" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-save me-1"></i>{{ $order->resi_code ? 'Update' : 'Add' }} Resi
                        </button>
                    </form>
                </div>
            </div>
        @endif
        
        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock me-2"></i>Order Timeline
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item {{ $order->status == 'pending' ? 'active' : '' }}">
                        <i class="fas fa-circle"></i>
                        <span>Order Placed</span>
                        <small>{{ $order->created_at->format('d M Y H:i') }}</small>
                    </div>
                    <div class="timeline-item {{ $order->status == 'processing' ? 'active' : '' }}">
                        <i class="fas fa-circle"></i>
                        <span>Processing</span>
                    </div>
                    <div class="timeline-item {{ $order->status == 'shipped' ? 'active' : '' }}">
                        <i class="fas fa-circle"></i>
                        <span>Shipped</span>
                    </div>
                    <div class="timeline-item {{ $order->status == 'completed' ? 'active' : '' }}">
                        <i class="fas fa-circle"></i>
                        <span>Completed</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #ddd;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item i {
    position: absolute;
    left: -26px;
    top: 2px;
    font-size: 10px;
    color: #ddd;
}

.timeline-item.active i {
    color: var(--primary-color);
}

.timeline-item span {
    display: block;
    font-weight: 600;
    color: #666;
}

.timeline-item.active span {
    color: var(--primary-color);
}

.timeline-item small {
    display: block;
    color: #999;
    font-size: 0.8rem;
}
</style>
@endpush