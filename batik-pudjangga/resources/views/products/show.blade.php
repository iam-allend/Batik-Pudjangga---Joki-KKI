@extends('layouts.app')

@section('title', $product->name . ' - Batik Pudjangga')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.index') }}">Shop</a></li>
            <li class="breadcrumb-item"><a href="{{ route('shop.' . $product->category) }}">{{ ucfirst($product->category) }}</a></li>
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        <!-- Product Images -->
        <div class="col-lg-6 mb-4">
            <div class="product-images">
                <!-- Main Image -->
                <div class="main-image mb-3">
                    <img src="{{ asset('storage/products/' . $product->image) }}" 
                         alt="{{ $product->name }}"
                         id="mainProductImage"
                         class="img-fluid rounded">
                    
                    @if($product->is_sale)
                        <span class="badge-sale">SALE {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}% OFF</span>
                    @endif
                    
                    @if($product->is_new)
                        <span class="badge-new">NEW</span>
                    @endif
                </div>

                <!-- Thumbnail Images -->
                @if($product->images->count() > 0)
                <div class="thumbnails">
                    <div class="row g-2">
                        <!-- Main Image Thumbnail -->
                        <div class="col-3">
                            <img src="{{ asset('storage/products/' . $product->image) }}" 
                                 alt="Thumbnail"
                                 class="img-thumbnail thumbnail-item active"
                                 onclick="changeMainImage(this.src)">
                        </div>
                        
                        <!-- Additional Images -->
                        @foreach($product->images as $image)
                        <div class="col-3">
                            <img src="{{ asset('storage/' . $image->image_path) }}" 
                                 alt="Thumbnail"
                                 class="img-thumbnail thumbnail-item"
                                 onclick="changeMainImage(this.src)">
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-6">
            <div class="product-info">
                <h1 class="product-title">{{ $product->name }}</h1>
                
                <!-- Category Badge -->
                <p class="text-muted mb-3">
                    <i class="fas fa-tag me-2"></i>
                    <a href="{{ route('shop.' . $product->category) }}" class="text-decoration-none">
                        {{ ucfirst($product->category) }}
                    </a>
                    @if($product->subcategory)
                        / {{ $product->subcategory }}
                    @endif
                </p>

                <!-- Price -->
                <div class="product-price mb-4">
                    @if($product->is_sale && $product->sale_price)
                        <h2 class="text-primary mb-0">
                            Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                        </h2>
                        <p class="text-muted mb-0">
                            <del>Rp {{ number_format($product->price, 0, ',', '.') }}</del>
                            <span class="badge bg-danger ms-2">
                                Save Rp {{ number_format($product->price - $product->sale_price, 0, ',', '.') }}
                            </span>
                        </p>
                    @else
                        <h2 class="text-primary mb-0">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </h2>
                    @endif
                </div>

                <!-- Stock Status -->
                <div class="mb-3">
                    @if($product->stock > 0)
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle"></i> In Stock ({{ $product->stock }} available)
                        </span>
                    @else
                        <span class="badge bg-danger">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    @endif
                </div>

                <!-- Description -->
                @if($product->description)
                <div class="product-description mb-4">
                    <h5>Description</h5>
                    <p class="text-muted">{{ $product->description }}</p>
                </div>
                @endif

                <!-- Add to Cart Form -->
                <form action="{{ route('cart.add') }}" method="POST" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <!-- Size Selection (if variants exist) -->
                    @if($product->variants->count() > 0)
                    <div class="mb-3">
                        <label class="form-label">Select Size *</label>
                        <div class="size-options">
                            @foreach($product->variants->unique('size') as $variant)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="size" 
                                       id="size_{{ $variant->size }}" 
                                       value="{{ $variant->size }}"
                                       {{ $loop->first ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label size-label" for="size_{{ $variant->size }}">
                                    {{ $variant->size }}
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <div class="input-group quantity-input" style="max-width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" name="quantity" id="quantity" 
                                   class="form-control text-center" 
                                   value="1" min="1" max="{{ $product->stock }}" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Notes (Optional) -->
                    <div class="mb-4">
                        <label class="form-label">Special Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Add custom request or notes..."></textarea>
                        <small class="text-muted">Example: color preference, special measurements, etc.</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-2 mb-4">
                        @auth
                            @if($product->stock > 0)
                                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                    <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                                </button>
                            @else
                                <button type="button" class="btn btn-secondary btn-lg flex-grow-1" disabled>
                                    Out of Stock
                                </button>
                            @endif

                            <!-- Wishlist Button -->
                            <button type="button" class="btn btn-outline-danger btn-lg" 
                                    onclick="toggleWishlist({{ $product->id }})"
                                    id="wishlistBtn">
                                <i class="fas fa-heart {{ $isInWishlist ? 'text-danger' : '' }}"></i>
                            </button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Purchase
                            </a>
                        @endauth
                    </div>
                </form>

                <!-- Product Meta -->
                <div class="product-meta">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>SKU:</strong> BP-{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                        </li>
                        <li class="mb-2">
                            <strong>Category:</strong> 
                            <a href="{{ route('shop.' . $product->category) }}">{{ ucfirst($product->category) }}</a>
                        </li>
                        @if($product->subcategory)
                        <li class="mb-2">
                            <strong>Subcategory:</strong> {{ $product->subcategory }}
                        </li>
                        @endif
                    </ul>
                </div>

                <!-- Share Buttons -->
                <div class="share-product mt-4">
                    <p class="mb-2"><strong>Share this product:</strong></p>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" 
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ url()->current() }}&text={{ $product->name }}" 
                           target="_blank" class="btn btn-sm btn-outline-info">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ $product->name }} {{ url()->current() }}" 
                           target="_blank" class="btn btn-sm btn-outline-success">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyLink()">
                            <i class="fas fa-link"></i> Copy Link
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="related-products mt-5">
        <h3 class="mb-4">You May Also Like</h3>
        <div class="row">
            @foreach($relatedProducts as $related)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                @include('components.product-card', ['product' => $related])
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>

<style>
.product-images .main-image {
    position: relative;
    overflow: hidden;
}

.product-images .main-image img {
    width: 100%;
    height: auto;
    max-height: 600px;
    object-fit: contain;
}

.badge-sale {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #dc3545;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    z-index: 1;
}

.badge-new {
    position: absolute;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    font-weight: bold;
    z-index: 1;
}

.thumbnail-item {
    cursor: pointer;
    transition: all 0.3s;
    border: 2px solid #dee2e6;
    height: 100px;
    object-fit: cover;
}

.thumbnail-item:hover,
.thumbnail-item.active {
    border-color: #0d6efd;
    transform: scale(1.05);
}

.product-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.product-price h2 {
    font-size: 2.5rem;
    font-weight: 700;
}

.size-label {
    padding: 10px 20px;
    border: 2px solid #dee2e6;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.form-check-input:checked + .size-label {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}

.quantity-input input {
    border-left: none;
    border-right: none;
}

.quantity-input button {
    width: 40px;
}

.product-meta a {
    color: #0d6efd;
    text-decoration: none;
}

.product-meta a:hover {
    text-decoration: underline;
}
</style>

<script>
// Change Main Image
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;
    
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(thumb => {
        thumb.classList.remove('active');
    });
    event.target.classList.add('active');
}

// Quantity Controls
function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value);
    
    if (current < max) {
        input.value = current + 1;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    const min = parseInt(input.getAttribute('min'));
    const current = parseInt(input.value);
    
    if (current > min) {
        input.value = current - 1;
    }
}

// Toggle Wishlist
function toggleWishlist(productId) {
    const btn = document.getElementById('wishlistBtn');
    const icon = btn.querySelector('i');
    
    fetch(`/wishlist/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.action === 'added') {
                icon.classList.add('text-danger');
                showNotification('Added to wishlist!', 'success');
            } else {
                icon.classList.remove('text-danger');
                showNotification('Removed from wishlist!', 'info');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Something went wrong!', 'danger');
    });
}

// Copy Link
function copyLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        showNotification('Link copied to clipboard!', 'success');
    });
}

// Show Notification
function showNotification(message, type) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        alert.remove();
    }, 3000);
}

// Add to Cart Success
@if(session('success'))
    showNotification('{{ session('success') }}', 'success');
@endif
</script>
@endsection