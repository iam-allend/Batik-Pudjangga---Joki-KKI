<div class="product-card">
    <div class="product-image">
        <a href="{{ route('product.show', $product) }}">
            <img src="{{ asset('storage/products/' . $product->image) }}" alt="{{ $product->name }}" class="img-fluid">
        </a>

        {{-- Wishlist Button - SIMPLE FORM POST --}}
        @auth
        @php
        $inWishlist = $isInWishlist ?? ($product->in_wishlist ?? false);
        @endphp

        <form action="{{ route('wishlist.toggle') }}" method="POST" class="wishlist-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">

            <button type="submit" class="btn-wishlist"
                title="{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}">

                <i class="fas fa-heart" style="
                            color: {{ $inWishlist ? '#e3342f' : '#000' }};
                            transition: transform 0.2s ease;
                    ">
                </i>

            </button>
        </form>
        @else
        <a href="{{ route('login') }}" class="btn-wishlist" title="Login to add to wishlist">
            <i class="fas fa-heart" style="color:#000;"></i>
        </a>
        @endauth



        {{-- Badges --}}
        @if($product->is_sale)
        <span class="badge-sale">
            SALE
            @if($product->sale_price)
            {{ round((($product->price - $product->sale_price) / $product->price) * 100) }}%
            @endif
        </span>
        @endif

        @if($product->is_new)
        <span class="badge-new">NEW</span>
        @endif

        @if($product->stock <= 0) <span class="badge-out">OUT OF STOCK</span>
            @endif
    </div>

    <div class="product-info">
        <h6 class="product-title mb-3">
            <a href="{{ route('product.show', $product) }}">
                {{ $product->name }}
            </a>
        </h6>

        <p class="product-category mt-0">
            <i class="fas fa-tag"></i> {{ ucfirst($product->category) }}
        </p>

        <div class="product-price">
            @if($product->is_sale && $product->sale_price)
            <span class="price-sale">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</span>
            <span class="price-original">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            @else
            <span class="price-regular">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
            @endif
        </div>

        <div class="product-actions">
            @if($product->stock > 0)
            <a href="{{ route('product.show', $product) }}" class="btn btn-primary btn-sm w-100">
                <i class="fas fa-eye"></i> View Details
            </a>
            @else
            <button class="btn btn-secondary btn-sm w-100" disabled>
                <i class="fas fa-ban"></i> Out of Stock
            </button>
            @endif
        </div>
    </div>
</div>

<style>
    /* FORCE COLOR */
    .btn-wishlist .wishlist-active {
        color: #e3342f !important;
        /* merah */
    }

    .btn-wishlist .wishlist-inactive {
        color: #000 !important;
        /* hitam */
    }

    /* optional smooth */
    .btn-wishlist i {
        transition: color 0.2s ease, transform 0.2s ease;
    }


    .product-card {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
        background: white;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-5px);
    }

    .product-image {
        position: relative;
        overflow: hidden;
        height: 250px;
        background: #f8f9fa;
    }

    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .product-image:hover img {
        transform: scale(1.1);
    }

    /* Wishlist Form */
    .wishlist-form {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }

    /* Wishlist Button */
    .btn-wishlist {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        border: none;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
        padding: 0;
        text-decoration: none;
    }

    .btn-wishlist:hover {
        transform: scale(1.1);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.3);
    }

    .btn-wishlist i {
        font-size: 18px;
        transition: all 0.3s;
    }


    .btn-wishlist:hover i {
        transform: scale(1.2);
    }

    /* Badges */
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
        z-index: 1;
        width: fit-content;
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
        z-index: 1;
    }

    .badge-out {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 10px 20px;
        border-radius: 5px;
        font-size: 14px;
        font-weight: bold;
        z-index: 1;
    }

    /* Product Info */
    .product-info {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .product-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .product-title a {
        color: #333;
        text-decoration: none;
    }

    .product-title a:hover {
        color: var(--primary-color, #0d6efd);
    }

    .product-category {
        font-size: 13px;
        color: #666;
        margin-bottom: 10px;
    }

    .product-category i {
        margin-right: 5px;
    }

    .product-price {
        margin-bottom: 15px;
    }

    .price-regular {
        font-size: 18px;
        font-weight: 700;
        color: var(--primary-color, #0d6efd);
    }

    .price-sale {
        font-size: 18px;
        font-weight: 700;
        color: #dc3545;
        margin-right: 8px;
    }

    .price-original {
        font-size: 14px;
        color: #999;
        text-decoration: line-through;
    }

    .product-actions {
        margin-top: auto;
    }
</style>