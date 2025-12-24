@extends('layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products Management')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-box me-2"></i>All Products</span>
        <a href="{{ route('admin.products.create') }}" class="btn btn-light btn-sm">
            <i class="fas fa-plus me-1"></i>Add New Product
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form action="{{ route('admin.products.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search products..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <form action="{{ route('admin.products.index') }}" method="GET">
                    <select class="form-select" name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="men" {{ request('category') == 'men' ? 'selected' : '' }}>Men</option>
                        <option value="women" {{ request('category') == 'women' ? 'selected' : '' }}>Women</option>
                        <option value="pants" {{ request('category') == 'pants' ? 'selected' : '' }}>Pants</option>
                        <option value="oneset" {{ request('category') == 'oneset' ? 'selected' : '' }}>One Set</option>
                    </select>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>
                                <img src="{{ asset('storage/products/' . $product->image) }}" 
                                     alt="{{ $product->name }}" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->is_new)
                                    <span class="badge bg-info ms-1">New</span>
                                @endif
                                @if($product->is_sale)
                                    <span class="badge bg-danger ms-1">Sale</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($product->category) }}</span>
                            </td>
                            <td>
                                @if($product->is_sale && $product->sale_price)
                                    <del class="text-muted">Rp {{ number_format($product->price, 0, ',', '.') }}</del>
                                    <br>
                                    <strong class="text-danger">Rp {{ number_format($product->sale_price, 0, ',', '.') }}</strong>
                                @else
                                    <strong>Rp {{ number_format($product->price, 0, ',', '.') }}</strong>
                                @endif
                            </td>
                            <td>
                                @if($product->stock <= 5 && $product->stock > 0)
                                    <span class="badge bg-warning">{{ $product->stock }}</span>
                                @elseif($product->stock == 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $product->deleted_at ? 'danger' : 'success' }}">
                                    {{ $product->deleted_at ? 'Inactive' : 'Active' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.products.show', $product) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" 
                                          method="POST" 
                                          onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
