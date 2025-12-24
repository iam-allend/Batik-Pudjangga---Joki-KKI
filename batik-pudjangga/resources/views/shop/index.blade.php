@extends('layouts.app')

@section('title', 'Shop All Products - Batik Pudjangga')

@section('content')
<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="page-title">Shop All Products</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Shop</li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="text-muted">Showing {{ $products->count() }} of {{ $products->total() }} products</p>
            </div>
        </div>
    </div>
</section>

<!-- Shop Content -->
<section class="shop-section py-5">
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filters-sidebar">
                    <h5 class="mb-4">Filters</h5>
                    
                    <!-- Category Filter -->
                    <div class="filter-group">
                        <h6>Categories</h6>
                        <div class="filter-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="" 
                                       {{ !request('category') ? 'checked' : '' }}
                                       onchange="applyFilters()">
                                <label class="form-check-label">All Products</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="men"
                                       {{ request('category') == 'men' ? 'checked' : '' }}
                                       onchange="applyFilters()">
                                <label class="form-check-label">Men</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="women"
                                       {{ request('category') == 'women' ? 'checked' : '' }}
                                       onchange="applyFilters()">
                                <label class="form-check-label">Women</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="pants"
                                       {{ request('category') == 'pants' ? 'checked' : '' }}
                                       onchange="applyFilters()">
                                <label class="form-check-label">Pants</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="category" value="oneset"
                                       {{ request('category') == 'oneset' ? 'checked' : '' }}
                                       onchange="applyFilters()">
                                <label class="form-check-label">One Set</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sort By -->
                    <div class="filter-group">
                        <h6>Sort By</h6>
                        <select class="form-select" name="sort" onchange="applyFilters()">
                            <option value="">Default</option>
                            <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                    
                    <!-- Clear Filters -->
                    <a href="{{ route('shop.index') }}" class="btn btn-outline-primary w-100 mt-3">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </a>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                @if($products->count() > 0)
                    <div class="row g-4">
                        @foreach($products as $product)
                            <div class="col-lg-4 col-md-6">
                                @include('components.product-card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-5">
                        {{ $products->links() }}
                    </div>
                @else
                    <div class="empty-state text-center py-5">
                        <i class="fas fa-box-open fa-5x text-muted mb-4"></i>
                        <h3>No Products Found</h3>
                        <p class="text-muted">Try adjusting your filters or search criteria</p>
                        <a href="{{ route('shop.index') }}" class="btn btn-primary mt-3">
                            View All Products
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const category = document.querySelector('input[name="category"]:checked').value;
    const sort = document.querySelector('select[name="sort"]').value;
    
    let url = new URL(window.location.href);
    
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    
    if (sort) {
        url.searchParams.set('sort', sort);
    } else {
        url.searchParams.delete('sort');
    }
    
    window.location.href = url.toString();
}
</script>
@endpush
