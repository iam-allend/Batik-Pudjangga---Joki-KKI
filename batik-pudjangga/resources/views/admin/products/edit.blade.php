@extends('layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Product Information
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="5">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Select Category</option>
                                <option value="men" {{ old('category', $product->category) == 'men' ? 'selected' : '' }}>Men</option>
                                <option value="women" {{ old('category', $product->category) == 'women' ? 'selected' : '' }}>Women</option>
                                <option value="pants" {{ old('category', $product->category) == 'pants' ? 'selected' : '' }}>Pants</option>
                                <option value="oneset" {{ old('category', $product->category) == 'oneset' ? 'selected' : '' }}>One Set</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subcategory</label>
                            <input type="text" class="form-control @error('subcategory') is-invalid @enderror" 
                                   name="subcategory" value="{{ old('subcategory', $product->subcategory) }}">
                            @error('subcategory')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   name="price" value="{{ old('price', $product->price) }}" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                   name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Current Image</label>
                        <div class="mb-2">
                            <img src="{{ asset('storage/products/' . $product->image) }}" 
                                 alt="{{ $product->name }}" 
                                 style="max-width: 200px; border-radius: 8px;">
                        </div>
                        <label class="form-label">Change Image</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               name="image" accept="image/*">
                        <small class="text-muted">Leave empty to keep current image</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Additional Settings</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_new" name="is_new" 
                                   value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_new">
                                Mark as New Product
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale" 
                                   value="1" {{ old('is_sale', $product->is_sale) ? 'checked' : '' }} onchange="toggleSaleFields()">
                            <label class="form-check-label" for="is_sale">
                                Put on Sale
                            </label>
                        </div>
                    </div>
                    
                    <div id="saleFields" style="display: {{ old('is_sale', $product->is_sale) ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale Price (Rp)</label>
                                <input type="number" class="form-control" name="sale_price" 
                                       value="{{ old('sale_price', $product->sale_price) }}" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale Start Date</label>
                                <input type="date" class="form-control" name="sale_start" 
                                       value="{{ old('sale_start', $product->sale_start ? $product->sale_start->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale End Date</label>
                                <input type="date" class="form-control" name="sale_end" 
                                       value="{{ old('sale_end', $product->sale_end ? $product->sale_end->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Product
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-chart-line me-2"></i>Product Stats
            </div>
            <div class="card-body">
                <p><strong>Created:</strong> {{ $product->created_at->format('d M Y') }}</p>
                <p><strong>Last Updated:</strong> {{ $product->updated_at->format('d M Y') }}</p>
                <p><strong>Total Orders:</strong> {{ $product->orderItems->count() }}</p>
                <p><strong>In Carts:</strong> {{ $product->carts->count() }}</p>
                <p><strong>In Wishlists:</strong> {{ $product->wishlists->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleSaleFields() {
    const isChecked = document.getElementById('is_sale').checked;
    document.getElementById('saleFields').style.display = isChecked ? 'block' : 'none';
}
</script>
@endpush