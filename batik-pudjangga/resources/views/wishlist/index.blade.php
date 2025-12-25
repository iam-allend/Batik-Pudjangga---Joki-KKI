@extends('layouts.app')

@section('title', 'My Wishlist - Batik Pudjangga')

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
                    <h5 class="mb-0">
                        <i class="fas fa-heart me-2"></i>My Wishlist
                        @if($wishlistItems->count() > 0)
                            <span class="badge bg-primary">{{ $wishlistItems->count() }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($wishlistItems->count() > 0)
                        <div class="row">
                            @foreach($wishlistItems as $wishlist)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="wishlist-card">
                                    <div class="wishlist-image">
                                        <a href="{{ route('product.show', $wishlist->product) }}">
                                            <img src="{{ asset('storage/products/' . $wishlist->product->image) }}" 
                                                 alt="{{ $wishlist->product->name }}"
                                                 class="img-fluid">
                                        </a>
                                        
                                        <!-- Remove Button -->
                                        <form action="{{ route('wishlist.remove', $wishlist->product) }}" 
                                              method="POST" 
                                              class="wishlist-remove"
                                              onsubmit="return confirm('Remove from wishlist?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>

                                        <!-- Badges -->
                                        @if($wishlist->product->is_sale)
                                            <span class="badge-sale">SALE</span>
                                        @endif
                                        @if($wishlist->product->is_new)
                                            <span class="badge-new">NEW</span>
                                        @endif
                                    </div>

                                    <div class="wishlist-content">
                                        <h6 class="mb-2">
                                            <a href="{{ route('product.show', $wishlist->product) }}" 
                                               class="text-decoration-none text-dark">
                                                {{ $wishlist->product->name }}
                                            </a>
                                        </h6>

                                        <p class="text-muted mb-2">
                                            <small>
                                                <i class="fas fa-tag me-1"></i>
                                                {{ ucfirst($wishlist->product->category) }}
                                            </small>
                                        </p>

                                        <div class="mb-3">
                                            @if($wishlist->product->is_sale && $wishlist->product->sale_price)
                                                <h5 class="text-primary mb-0">
                                                    Rp {{ number_format($wishlist->product->sale_price, 0, ',', '.') }}
                                                </h5>
                                                <small class="text-muted">
                                                    <del>Rp {{ number_format($wishlist->product->price, 0, ',', '.') }}</del>
                                                </small>
                                            @else
                                                <h5 class="text-primary mb-0">
                                                    Rp {{ number_format($wishlist->product->price, 0, ',', '.') }}
                                                </h5>
                                            @endif
                                        </div>

                                        <!-- Stock Status -->
                                        @if($wishlist->product->stock > 0)
                                            <span class="badge bg-success mb-3">
                                                <i class="fas fa-check"></i> In Stock
                                            </span>
                                        @else
                                            <span class="badge bg-danger mb-3">
                                                <i class="fas fa-times"></i> Out of Stock
                                            </span>
                                        @endif

                                        <!-- Actions -->
                                        <div class="d-flex gap-2">
                                            @if($wishlist->product->stock > 0)
                                                <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                                                    @csrf
                                                    <input type="hidden" name="product_id" value="{{ $wishlist->product->id }}">
                                                    <input type="hidden" name="quantity" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                                        <i class="fas fa-cart-plus"></i> Add to Cart
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-secondary btn-sm w-100" disabled>
                                                    Out of Stock
                                                </button>
                                            @endif
                                            
                                            <a href="{{ route('product.show', $wishlist->product) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($wishlistItems->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $wishlistItems->links() }}
                        </div>
                        @endif
                    @else
                        <!-- Empty Wishlist -->
                        <div class="text-center py-5">
                            <i class="fas fa-heart fa-5x text-muted mb-4"></i>
                            <h4>Your wishlist is empty</h4>
                            <p class="text-muted mb-4">Save your favorite items to your wishlist!</p>
                            <a href="{{ route('shop.index') }}" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Browse Products
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wishlist-card {
    border: 1px solid #dee2e6;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s;
}

.wishlist-card:hover {
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transform: translateY(-5px);
}

.wishlist-image {
    position: relative;
    overflow: hidden;
    height: 250px;
    background: #f8f9fa;
}

.wishlist-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.wishlist-image:hover img {
    transform: scale(1.1);
}

.wishlist-remove {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
}

.badge-sale {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #dc3545;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.badge-new {
    position: absolute;
    top: 45px;
    left: 10px;
    background: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: bold;
}

.wishlist-content {
    padding: 15px;
}

.wishlist-content h6 {
    min-height: 40px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection