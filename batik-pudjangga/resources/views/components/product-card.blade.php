<div class="product-card">
    <div class="product-image">
        <a href="{{ route('product.show', $product) }}">
            <img src="{{ asset('storage/products/' . $product->image) }}" 
                 alt="{{ $product->name }}"
                 onerror="this.src='https://via.placeholder.com/300x400?text={{ urlencode($product->name) }}'">
        </a>
        
        <!-- Badges -->
        <div class="product-badges">
            @if($product->is_new)
                <span class="badge badge-new">New</span>
            @endif
            @if($product->is_sale && $product->isSaleActive())
                <span class="badge badge-sale">-{{ $product->discount_percentage }}%</span>
            @endif
            @if($product->stock <= 0)
                <span class="badge badge-out">Out of Stock</span>
            @elseif($product->stock <= 5)
                <span class="badge badge-low">Only {{ $product->stock }} left</span>
            @endif
        </div>
        
        <!-- Quick Actions -->
        <div class="product-actions">
            @auth
                <!-- Wishlist Button -->
                <button type="button" class="btn-action btn-wishlist" 
                        onclick="toggleWishlist({{ $product->id }}, this)"
                        data-product-id="{{ $product->id }}"
                        title="{{ auth()->user()->wishlists->contains('product_id', $product->id) ? 'Remove from Wishlist' : 'Add to Wishlist' }}">
                    <i class="fas fa-heart {{ auth()->user()->wishlists->contains('product_id', $product->id) ? 'text-danger' : '' }}"></i>
                </button>
            @else
                <a href="{{ route('login') }}" class="btn-action" title="Add to Wishlist">
                    <i class="fas fa-heart"></i>
                </a>
            @endauth
            
            <!-- Quick View -->
            <a href="{{ route('product.show', $product) }}" class="btn-action" title="Quick View">
                <i class="fas fa-eye"></i>
            </a>
        </div>
    </div>
    
    <div class="product-info">
        <div class="product-category">
            <span class="badge bg-secondary">{{ ucfirst($product->category) }}</span>
        </div>
        
        <h5 class="product-name">
            <a href="{{ route('product.show', $product) }}">{{ $product->name }}</a>
        </h5>
        
        <div class="product-price">
            @if($product->is_sale && $product->sale_price && $product->isSaleActive())
                <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                <span class="price-sale">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
            @else
                <span class="price-current">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            @endif
        </div>
        
        <form action="{{ route('cart.add.inCard') }}" method="POST" id="addToCartForm">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <div class="product-footer">
                @if($product->stock > 0)
                    @auth
                        <input type="number" name="quantity" id="quantity" class="form-control text-center"
                                value="1" min="1" max="{{ $product->stock }}" required hidden>
                        <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                            <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                        </button>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100">
                            <i class="fas fa-sign-in-alt me-1"></i>Login to Purchase
                        </a>
                    @endauth
                @else
                    <button class="btn btn-secondary btn-sm w-100" disabled>
                        <i class="fas fa-times me-1"></i>Out of Stock
                    </button>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
.product-card {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    padding-top: 133%; /* 3:4 Aspect Ratio */
    overflow: hidden;
    background: #f8f8f8;
}

.product-image img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s;
}

.product-card:hover .product-image img {
    transform: scale(1.1);
}

.product-badges {
    position: absolute;
    top: 15px;
    left: 15px;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.product-badges .badge {
    padding: 5px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 20px;
    display: inline-block;
}

.badge-new {
    background: #28a745;
    color: white;
}

.badge-sale {
    background: #dc3545;
    color: white;
}

.badge-out {
    background: #6c757d;
    color: white;
}

.badge-low {
    background: #ffc107;
    color: #333;
}

.product-actions {
    position: absolute;
    top: 15px;
    right: 15px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s;
}

.product-card:hover .product-actions {
    opacity: 1;
    transform: translateX(0);
}

.btn-action {
    width: 40px;
    height: 40px;
    background: white;
    border: none;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s;
}

.btn-action:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
}

.btn-action.active,
.btn-wishlist .fa-heart.text-danger {
    color: #dc3545 !important;
}

.product-info {
    padding: 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.product-category {
    margin-bottom: 10px;
}

.product-name {
    font-size: 1rem;
    margin-bottom: 10px;
    flex-grow: 1;
}

.product-name a {
    color: var(--text-color);
    text-decoration: none;
    transition: color 0.3s;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-name a:hover {
    color: var(--primary-color);
}

.product-price {
    margin-bottom: 15px;
}

.price-original {
    text-decoration: line-through;
    color: #999;
    font-size: 0.9rem;
    margin-right: 8px;
}

.price-sale {
    color: #dc3545;
    font-weight: 700;
    font-size: 1.1rem;
}

.price-current {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.1rem;
}

.btn-add-cart {
    transition: all 0.3s;
}

.btn-add-cart:active {
    transform: scale(0.95);
}

@media (max-width: 576px) {
    .product-actions {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<script>
// Quick Add to Cart
function quickAddToCart(productId, button) {
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    // Use jQuery AJAX with proper CSRF handling
    $.ajax({
        url: '/api/cart/add',
        method: 'POST',
        data: JSON.stringify({
            product_id: productId,
            quantity: 1
        }),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success) {
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                button.classList.remove('btn-primary');
                button.classList.add('btn-success');
                
                // Update cart count
                if (typeof updateCartCount === 'function') {
                    updateCartCount();
                }
                
                // Show success toast
                showToast(data.message || 'Product added to cart!', 'success');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('btn-success');
                    button.classList.add('btn-primary');
                    button.disabled = false;
                }, 2000);
            } else {
                button.innerHTML = originalHTML;
                button.disabled = false;
                showToast(data.message || 'Failed to add to cart', 'error');
            }
        },
        error: function(xhr, status, error) {
            button.innerHTML = originalHTML;
            button.disabled = false;
            
            let errorMessage = 'An error occurred';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showToast(errorMessage, 'error');
            console.error('Add to cart error:', xhr.responseJSON);
        }
    });
}

// Toggle Wishlist
function toggleWishlist(productId, button) {
    const icon = button.querySelector('i');
    const isInWishlist = icon.classList.contains('text-danger');
    
    $.ajax({
        url: '/api/wishlist/toggle/' + productId,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(data) {
            if (data.success) {
                if (data.data.in_wishlist) {
                    icon.classList.add('text-danger');
                    button.title = 'Remove from Wishlist';
                } else {
                    icon.classList.remove('text-danger');
                    button.title = 'Add to Wishlist';
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Failed to update wishlist', 'error');
            }
        },
        error: function(xhr, status, error) {
            let errorMessage = 'Failed to update wishlist';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showToast(errorMessage, 'error');
            console.error('Wishlist error:', xhr.responseJSON);
        }
    });
}

// Show Toast Notification
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
        ${message}
    `;
    
    toastContainer.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
    `;
    document.body.appendChild(container);
    return container;
}
</script>

<style>
.toast-notification {
    background: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    margin-bottom: 10px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s;
    min-width: 250px;
}

.toast-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.toast-success {
    border-left: 4px solid #28a745;
    color: #28a745;
}

.toast-error {
    border-left: 4px solid #dc3545;
    color: #dc3545;
}
</style>