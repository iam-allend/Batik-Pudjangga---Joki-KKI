@extends('layouts.app')

@section('title', 'Checkout - Batik Pudjangga')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">
        <i class="fas fa-credit-card me-2"></i>Checkout
    </h2>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
        @csrf
        <input type="hidden" name="selected_items" value="{{ implode(',', $selectedItems) }}">

        <div class="row">
            <!-- Left Column: Shipping & Payment -->
            <div class="col-lg-8 mb-4">
                <!-- Shipping Address -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Shipping Address</h5>
                        @if($addresses->count() > 0)
                            <a href="{{ route('address.create') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-plus"></i> Add New
                            </a>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($addresses->count() > 0)
                            @foreach($addresses as $address)
                            <div class="form-check address-card {{ $address->is_default ? 'selected' : '' }}">
                                <input class="form-check-input" 
                                       type="radio" 
                                       name="address_id" 
                                       id="address_{{ $address->id }}" 
                                       value="{{ $address->id }}"
                                       data-province="{{ $address->province }}"
                                       {{ $address->is_default ? 'checked' : '' }}
                                       onchange="updateShippingCost()"
                                       required>
                                <label class="form-check-label w-100" for="address_{{ $address->id }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>{{ $address->recipient_name }}</strong>
                                            @if($address->is_default)
                                                <span class="badge bg-primary ms-2">Default</span>
                                            @endif
                                            <p class="mb-0 text-muted">{{ $address->phone }}</p>
                                            <p class="mb-0">
                                                {{ $address->address }}<br>
                                                {{ $address->city }}, {{ $address->province }}<br>
                                                {{ $address->postal_code }}
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                You don't have any saved addresses.
                                <a href="{{ route('address.create') }}" class="alert-link">Add one now</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Shipping Method -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Shipping Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="shipping_method" 
                                   id="shipping_regular" 
                                   value="regular"
                                   checked
                                   onchange="updateShippingCost()">
                            <label class="form-check-label w-100" for="shipping_regular">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>Regular Shipping</strong>
                                        <p class="text-muted mb-0">Estimated 5-7 business days</p>
                                    </div>
                                    <strong id="regularCost">Calculating...</strong>
                                </div>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="shipping_method" 
                                   id="shipping_express" 
                                   value="express"
                                   onchange="updateShippingCost()">
                            <label class="form-check-label w-100" for="shipping_express">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>Express Shipping</strong>
                                        <p class="text-muted mb-0">Estimated 2-3 business days</p>
                                    </div>
                                    <strong id="expressCost">Calculating...</strong>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Method</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="payment_method" 
                                   id="payment_transfer" 
                                   value="transfer"
                                   checked
                                   required>
                            <label class="form-check-label w-100" for="payment_transfer">
                                <strong>Bank Transfer</strong>
                                <p class="text-muted mb-0">Transfer to our bank account</p>
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="radio" 
                                   name="payment_method" 
                                   id="payment_cod" 
                                   value="cod">
                            <label class="form-check-label w-100" for="payment_cod">
                                <strong>Cash on Delivery (COD)</strong>
                                <p class="text-muted mb-0">Pay when you receive your order</p>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Order Items -->
                        <div class="order-items mb-3">
                            @foreach($cartItems as $item)
                            <div class="d-flex mb-2">
                                <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                     alt="{{ $item->product->name }}"
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                <div class="ms-2 flex-grow-1">
                                    <p class="mb-0">
                                        <small>{{ $item->product->name }}</small>
                                    </p>
                                    <small class="text-muted">
                                        {{ $item->quantity }}x Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </small>
                                </div>
                                <strong>
                                    <small>Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</small>
                                </strong>
                            </div>
                            @endforeach
                        </div>

                        <hr>

                        <!-- Price Breakdown -->
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping:</span>
                            <strong id="shippingCostDisplay">Calculating...</strong>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total:</h5>
                            <h5 class="text-primary" id="grandTotal">Rp {{ number_format($subtotal, 0, ',', '.') }}</h5>
                        </div>

                        <input type="hidden" name="shipping_cost" id="shippingCostInput" value="0">

                        <button type="submit" class="btn btn-primary w-100 mb-2" id="placeOrderBtn">
                            <i class="fas fa-check-circle me-2"></i>Place Order
                        </button>

                        <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to Cart
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.address-card {
    padding: 15px;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s;
}

.address-card:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}

.address-card.selected {
    border-color: #0d6efd;
    background: #e7f1ff;
}

.address-card input[type="radio"]:checked + label {
    color: #0d6efd;
}
</style>

<script>
const subtotal = {{ $subtotal }};
let shippingCost = 0;

// Update Shipping Cost based on selected address and method
function updateShippingCost() {
    const selectedAddress = document.querySelector('input[name="address_id"]:checked');
    const selectedMethod = document.querySelector('input[name="shipping_method"]:checked');
    
    if (!selectedAddress) {
        document.getElementById('placeOrderBtn').disabled = true;
        return;
    }
    
    const province = selectedAddress.getAttribute('data-province');
    const method = selectedMethod ? selectedMethod.value : 'regular';
    
    // Fetch shipping cost from API
    fetch(`/api/shipping/get-cost?province=${encodeURIComponent(province)}&method=${method}`, {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            shippingCost = data.cost;
            
            // Update display
            document.getElementById('regularCost').textContent = formatRupiah(data.regular_cost);
            document.getElementById('expressCost').textContent = formatRupiah(data.express_cost);
            document.getElementById('shippingCostDisplay').textContent = formatRupiah(shippingCost);
            document.getElementById('shippingCostInput').value = shippingCost;
            
            // Update grand total
            const grandTotal = subtotal + shippingCost;
            document.getElementById('grandTotal').textContent = formatRupiah(grandTotal);
            
            // Enable place order button
            document.getElementById('placeOrderBtn').disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateShippingCost();
});
</script>
@endsection