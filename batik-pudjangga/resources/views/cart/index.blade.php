@extends('layouts.app')

@section('title', 'Shopping Cart - Batik Pudjangga')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">
        <i class="fas fa-shopping-cart me-2"></i>Shopping Cart
    </h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($cartItems->count() > 0)
    <div class="row">
        <!-- Cart Items -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cart Items ({{ $cartItems->count() }})</h5>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" 
                               onchange="toggleSelectAll(this)">
                        <label class="form-check-label" for="selectAll">
                            Select All
                        </label>
                    </div>
                </div>
                <div class="card-body p-0">
                    @foreach($cartItems as $item)
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <!-- Checkbox -->
                            <div class="col-auto">
                                <div class="form-check">
                                    <input class="form-check-input item-checkbox" 
                                           type="checkbox" 
                                           name="selected_items[]"
                                           value="{{ $item->id }}"
                                           data-price="{{ $item->price * $item->quantity }}"
                                           onchange="updateTotal()">
                                </div>
                            </div>

                            <!-- Product Image -->
                            <div class="col-auto">
                                <img src="{{ asset('storage/products/' . $item->product->image) }}" 
                                     alt="{{ $item->product->name }}"
                                     class="cart-item-image">
                            </div>

                            <!-- Product Info -->
                            <div class="col">
                                <h6 class="mb-1">
                                    <a href="{{ route('product.show', $item->product) }}" 
                                       class="text-decoration-none text-dark">
                                        {{ $item->product->name }}
                                    </a>
                                </h6>
                                <p class="text-muted mb-1">
                                    @if($item->size)
                                        Size: {{ $item->size }} | 
                                    @endif
                                    Price: Rp {{ number_format($item->price, 0, ',', '.') }}
                                </p>
                                @if($item->notes)
                                    <p class="text-muted mb-0">
                                        <small><i>Note: {{ $item->notes }}</i></small>
                                    </p>
                                @endif
                            </div>

                            <!-- Quantity Controls -->
                            <div class="col-auto">
                                <form action="{{ route('cart.update', $item) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <div class="input-group quantity-input">
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                type="submit" 
                                                name="action" 
                                                value="decrease"
                                                {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="text" 
                                               class="form-control form-control-sm text-center" 
                                               value="{{ $item->quantity }}" 
                                               readonly
                                               style="width: 50px;">
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                type="submit" 
                                                name="action" 
                                                value="increase"
                                                {{ $item->quantity >= $item->product->stock ? 'disabled' : '' }}>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Subtotal -->
                            <div class="col-auto text-end">
                                <h6 class="mb-0">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</h6>
                            </div>

                            <!-- Remove Button -->
                            <div class="col-auto">
                                <form action="{{ route('cart.remove', $item) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Remove this item from cart?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-link text-danger p-0">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Selected Items:</span>
                        <span id="selectedCount">0</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span>Subtotal:</span>
                        <strong id="subtotalAmount">Rp 0</strong>
                    </div>
                    
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Shipping cost will be calculated at checkout
                        </small>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-3">
                        <h5>Total:</h5>
                        <h5 class="text-primary" id="totalAmount">Rp 0</h5>
                    </div>

                    <form action="{{ route('cart.checkout.selected') }}" method="POST" id="checkoutForm">
                        @csrf
                        <input type="hidden" name="selected_items" id="selectedItemsInput">
                        
                        <button type="submit" class="btn btn-primary w-100 mb-2" id="checkoutBtn" disabled>
                            <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                        </button>
                    </form>

                    <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
    @else
    <!-- Empty Cart -->
    <div class="text-center py-5">
        <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
        <h3>Your cart is empty</h3>
        <p class="text-muted mb-4">Add some products to your cart to get started!</p>
        <a href="{{ route('shop.index') }}" class="btn btn-primary btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Start Shopping
        </a>
    </div>
    @endif
</div>

<style>
.cart-item {
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    transition: background 0.3s;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item:hover {
    background: #f8f9fa;
}

.cart-item-image {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 8px;
}

.quantity-input {
    width: 120px;
}

.quantity-input input {
    border-left: none;
    border-right: none;
}

.card.sticky-top {
    position: sticky;
    top: 100px;
}
</style>

<script>
// Toggle Select All
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateTotal();
}

// Update Total
function updateTotal() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    let total = 0;
    let count = 0;
    const selectedIds = [];

    checkboxes.forEach(cb => {
        total += parseFloat(cb.getAttribute('data-price'));
        count++;
        selectedIds.push(cb.value);
    });

    // Update display
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('subtotalAmount').textContent = formatRupiah(total);
    document.getElementById('totalAmount').textContent = formatRupiah(total);

    // Update hidden input
    document.getElementById('selectedItemsInput').value = selectedIds.join(',');

    // Enable/disable checkout button
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (count > 0) {
        checkoutBtn.disabled = false;
    } else {
        checkoutBtn.disabled = true;
    }

    // Update "Select All" checkbox
    const selectAllCheckbox = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    selectAllCheckbox.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
}

// Format Rupiah
function formatRupiah(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
});
</script>
@endsection