@extends('layouts.app')

@section('title', 'Payment - Order #' . $order->order_code)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Success Alert -->
            <div class="alert alert-success text-center">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h4>Order Placed Successfully!</h4>
                <p class="mb-0">Order #{{ $order->order_code }}</p>
            </div>

            <!-- Payment Instructions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Instructions</h5>
                </div>
                <div class="card-body">
                    @if($order->payment_method === 'transfer')
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Please complete your payment within <strong>24 hours</strong> to avoid automatic cancellation.
                        </div>

                        <h6>Transfer to:</h6>
                        <div class="bank-info mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Bank BCA</h6>
                                            <p class="mb-1">Account Number: <strong>1234567890</strong></p>
                                            <p class="mb-0">Account Name: <strong>Batik Pudjangga</strong></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6>Bank Mandiri</h6>
                                            <p class="mb-1">Account Number: <strong>0987654321</strong></p>
                                            <p class="mb-0">Account Name: <strong>Batik Pudjangga</strong></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <strong>Transfer Amount:</strong>
                            <h3 class="mb-0">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h3>
                            <small>Please transfer the exact amount including unique code</small>
                        </div>

                        <h6>After Payment:</h6>
                        <ol>
                            <li>Take a screenshot of your payment receipt</li>
                            <li>Go to <a href="{{ route('orders.show', $order) }}">Order Details</a></li>
                            <li>Upload your payment proof</li>
                            <li>Wait for admin confirmation (max 1x24 hours)</li>
                        </ol>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            You selected <strong>Cash on Delivery (COD)</strong>
                        </div>

                        <h6>Payment Instructions:</h6>
                        <ol>
                            <li>Please prepare the exact amount when your order arrives</li>
                            <li>Amount to pay: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></li>
                            <li>Payment can be made in cash to the courier</li>
                            <li>Make sure to check the package before payment</li>
                        </ol>
                    @endif
                </div>
            </div>

            <!-- Order Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Code:</span>
                        <strong>{{ $order->order_code }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Order Date:</span>
                        <span>{{ $order->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Items:</span>
                        <span>{{ $order->items->count() }} item(s)</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Shipping Cost:</span>
                        <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <h5>Total:</h5>
                        <h5 class="text-primary">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="text-center mt-4">
                <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-eye me-2"></i>View Order Details
                </a>
                <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
@endsection