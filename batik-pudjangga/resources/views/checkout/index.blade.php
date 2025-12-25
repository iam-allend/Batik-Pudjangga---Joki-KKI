@extends('layouts.app')

@section('title', 'Checkout - Batik Pudjangga')

@section('content')
<section class="checkout-section py-5">
    <div class="container">
        <h2 class="mb-4">Checkout</h2>
        
        <form action="{{ route('checkout.process') }}" method="POST" id="checkoutForm">
            @csrf
            
            <div class="row">
                <!-- Shipping Information -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <!-- Saved Addresses -->
                            @if($addresses->count() > 0)
                                <div class="mb-4">
                                    <h6>Select Saved Address</h6>
                                    @foreach($addresses as $address)
                                        <div class="form-check address-option mb-3">
                                            <input class="form-check-input" type="radio" 
                                                   name="address_id" value="{{ $address->id }}"
                                                   id="address{{ $address->id }}"
                                                   data-name="{{ $address->recipient_name }}"
                                                   data-address="{{ $address->address }}"
                                                   data-city="{{ $address->city }}"
                                                   data-province="{{ $address->province }}"
                                                   data-postal="{{ $address->postal_code }}"
                                                   data-phone="{{ $address->phone }}"
                                                   {{ $address->is_default ? 'checked' : '' }}
                                                   onchange="fillAddress(this)">
                                            <label class="form-check-label w-100" for="address{{ $address->id }}">
                                                <strong>{{ $address->recipient_name }}</strong>
                                                @if($address->is_default)
                                                    <span class="badge bg-success ms-2">Default</span>
                                                @endif
                                                <br>
                                                <small>{{ $address->address }}, {{ $address->city }}, {{ $address->province }} {{ $address->postal_code }}</small>
                                                <br>
                                                <small>{{ $address->phone }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                    <hr>
                                    <p class="text-muted">Or enter new address below:</p>
                                </div>
                            @endif
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="recipient_name" 
                                           id="recipient_name" value="{{ old('recipient_name', $defaultAddress->recipient_name ?? '') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="phone" 
                                           id="phone" value="{{ old('phone', $defaultAddress->phone ?? '') }}" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Full Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="address" id="address" 
                                          rows="3" required>{{ old('address', $defaultAddress->address ?? '') }}</textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="city" 
                                           id="city" value="{{ old('city', $defaultAddress->city ?? '') }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Province <span class="text-danger">*</span></label>
                                    <select class="form-select" name="province" id="province" 
                                            onchange="updateShipping()" required>
                                        <option value="">Select Province</option>
                                        @foreach($provinces as $prov)
                                            <option value="{{ $prov->province }}" 
                                                    data-regular="{{ $prov->cost_regular }}"
                                                    data-express="{{ $prov->cost_express }}"
                                                    {{ old('province', $defaultAddress->province ?? '') == $prov->province ? 'selected' : '' }}>
                                                {{ $prov->province }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Postal Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="postal_code" 
                                           id="postal_code" value="{{ old('postal_code', $defaultAddress->postal_code ?? '') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Method -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipping Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="shipping_method" 
                                       value="regular" id="regular" checked onchange="updateShipping()">
                                <label class="form-check-label" for="regular">
                                    <strong>Regular Shipping</strong>
                                    <span class="text-primary ms-2" id="regular-cost">Rp 0</span><br>
                                    <small class="text-muted">Estimated delivery: 3-5 business days</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="shipping_method" 
                                       value="express" id="express" onchange="updateShipping()">
                                <label class="form-check-label" for="express">
                                    <strong>Express Shipping</strong>
                                    <span class="text-primary ms-2" id="express-cost">Rp 0</span><br>
                                    <small class="text-muted">Estimated delivery: 1-2 business days</small>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       value="transfer" id="transfer" checked>
                                <label class="form-check-label" for="transfer">
                                    <strong>Bank Transfer</strong><br>
                                    <small class="text-muted">Pay via bank transfer</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" 
                                       value="cod" id="cod">
                                <label class="form-check-label" for="cod">
                                    <strong>Cash on Delivery (COD)</strong><br>
                                    <small class="text-muted">Pay when you receive the product</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 100px;">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <!-- Cart Items -->
                            <div class="order-items mb-3">
                                @foreach($cartItems as $item)
                                    <div class="order-item d-flex mb-3">
                                        <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                             alt="{{ $item->product->name }}"
                                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <div class="ms-3 flex-grow-1">
                                            <h6 class="mb-1">{{ $item->product->name }}</h6>
                                            <small class="text-muted">
                                                @if($item->size) Size: {{ $item->size }}<br> @endif
                                                Qty: {{ $item->quantity }}
                                            </small>
                                        </div>
                                        <strong>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                                    </div>
                                @endforeach
                            </div>
                            
                            <hr>
                            
                            <!-- Price Summary -->
                            <div class="price-summary">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Shipping Cost:</span>
                                    <strong id="shippingDisplay">Rp 0</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="text-primary" id="totalDisplay">
                                        Rp {{ number_format($subtotal, 0, ',', '.') }}
                                    </strong>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100" id="btnSubmit">
                                <i class="fas fa-lock me-2"></i>Place Order
                            </button>
                            
                            <a href="{{ route('cart.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fas fa-arrow-left me-2"></i>Back to Cart
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>


@push('scripts')
<script>
    const subtotal = {{ $subtotal }};

    function fillAddress(radio) {
        if (radio.checked) {
            document.getElementById('recipient_name').value = radio.dataset.name || '';
            document.getElementById('address').value = radio.dataset.address || '';
            document.getElementById('city').value = radio.dataset.city || '';
            document.getElementById('province').value = radio.dataset.province || '';
            document.getElementById('postal_code').value = radio.dataset.postal || '';
            document.getElementById('phone').value = radio.dataset.phone || '';
            updateShipping();
        }
    }

    function updateShipping() {
        const provinceSelect = document.getElementById('province');
        const selectedOption = provinceSelect.options[provinceSelect.selectedIndex];
        
        // CRITICAL FIX: Check if province is selected
        if (!selectedOption || !selectedOption.value) {
            console.log('No province selected');
            document.getElementById('shippingDisplay').textContent = 'Rp 0';
            document.getElementById('totalDisplay').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('regular-cost').textContent = 'Rp 0';
            document.getElementById('express-cost').textContent = 'Rp 0';
            return;
        }
        
        // CRITICAL FIX: Get values with fallback to 0
        const regularCostStr = selectedOption.dataset.regular || '0';
        const expressCostStr = selectedOption.dataset.express || '0';
        
        // CRITICAL FIX: Parse and validate
        const regularCost = parseInt(regularCostStr) || 0;
        const expressCost = parseInt(expressCostStr) || 0;
        
        // Debug log (remove in production)
        console.log('Selected Province:', selectedOption.value);
        console.log('Regular Cost:', regularCost);
        console.log('Express Cost:', expressCost);
        
        // CRITICAL FIX: Check if costs are valid numbers
        if (regularCost === 0 || expressCost === 0) {
            console.error('Invalid shipping costs for province:', selectedOption.value);
            alert('Shipping cost not available for this province. Please contact admin.');
            return;
        }
        
        // Update cost labels
        document.getElementById('regular-cost').textContent = 'Rp ' + regularCost.toLocaleString('id-ID');
        document.getElementById('express-cost').textContent = 'Rp ' + expressCost.toLocaleString('id-ID');
        
        // Get selected shipping method
        const methodRadio = document.querySelector('input[name="shipping_method"]:checked');
        if (!methodRadio) {
            console.error('No shipping method selected');
            return;
        }
        
        const method = methodRadio.value;
        const shippingCost = method === 'express' ? expressCost : regularCost;
        
        // Update display
        document.getElementById('shippingDisplay').textContent = 'Rp ' + shippingCost.toLocaleString('id-ID');
        
        const total = subtotal + shippingCost;
        document.getElementById('totalDisplay').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }

    // Update on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Page loaded, updating shipping...');
        console.log('Subtotal:', subtotal);
        
        // Add event listeners to shipping method radios
        const shippingRadios = document.querySelectorAll('input[name="shipping_method"]');
        shippingRadios.forEach(radio => {
            radio.addEventListener('change', updateShipping);
        });
        
        // Initial update
        updateShipping();
    });

    // Prevent double submission
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit');
        
        // Final validation before submit
        const provinceSelect = document.getElementById('province');
        if (!provinceSelect.value) {
            e.preventDefault();
            alert('Please select a province!');
            return false;
        }
        
        const shippingDisplay = document.getElementById('shippingDisplay').textContent;
        if (shippingDisplay === 'Rp 0' || shippingDisplay.includes('NaN')) {
            e.preventDefault();
            alert('Please select a valid province with shipping cost!');
            return false;
        }
        
        // Disable button
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    });

</script>
@endpush

<style>
.address-option {
    border: 2px solid #e0e0e0;
    padding: 15px;
    border-radius: 10px;
    transition: all 0.3s;
    cursor: pointer;
}

.address-option:has(input:checked) {
    border-color: var(--primary-color);
    background: var(--light-color);
}

.address-option:hover {
    border-color: var(--primary-color);
}
</style>
@endsection