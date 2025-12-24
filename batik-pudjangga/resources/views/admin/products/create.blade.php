@extends('layouts.admin')

@section('title', 'Add New Product')
@section('page-title', 'Add New Product')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus me-2"></i>Product Information
            </div>
            <div class="card-body">
                <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  name="description" rows="5">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" name="category" required>
                                <option value="">Select Category</option>
                                <option value="men" {{ old('category') == 'men' ? 'selected' : '' }}>Men</option>
                                <option value="women" {{ old('category') == 'women' ? 'selected' : '' }}>Women</option>
                                <option value="pants" {{ old('category') == 'pants' ? 'selected' : '' }}>Pants</option>
                                <option value="oneset" {{ old('category') == 'oneset' ? 'selected' : '' }}>One Set</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Subcategory</label>
                            <input type="text" class="form-control @error('subcategory') is-invalid @enderror" 
                                   name="subcategory" value="{{ old('subcategory') }}">
                            @error('subcategory')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   name="price" value="{{ old('price') }}" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('stock') is-invalid @enderror" 
                                   name="stock" value="{{ old('stock', 0) }}" min="0" required>
                            @error('stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Product Image <span class="text-danger">*</span></label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" 
                               name="image" accept="image/*" required>
                        <small class="text-muted">Max size: 2MB. Format: JPG, PNG</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Additional Settings</h6>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_new" name="is_new" 
                                   value="1" {{ old('is_new') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_new">
                                Mark as New Product
                            </label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_sale" name="is_sale" 
                                   value="1" {{ old('is_sale') ? 'checked' : '' }} onchange="toggleSaleFields()">
                            <label class="form-check-label" for="is_sale">
                                Put on Sale
                            </label>
                        </div>
                    </div>
                    
                    <div id="saleFields" style="display: {{ old('is_sale') ? 'block' : 'none' }};">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale Price (Rp)</label>
                                <input type="number" class="form-control" name="sale_price" 
                                       value="{{ old('sale_price') }}" min="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale Start Date</label>
                                <input type="date" class="form-control" name="sale_start" 
                                       value="{{ old('sale_start') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sale End Date</label>
                                <input type="date" class="form-control" name="sale_end" 
                                       value="{{ old('sale_end') }}">
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Product
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
                <i class="fas fa-info-circle me-2"></i>Tips
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Use clear and descriptive product names</li>
                    <li>Add high-quality product images</li>
                    <li>Write detailed product descriptions</li>
                    <li>Set competitive prices</li>
                    <li>Keep stock information up to date</li>
                </ul>
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
