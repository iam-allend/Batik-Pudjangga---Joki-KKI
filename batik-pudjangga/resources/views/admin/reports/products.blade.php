@extends('layouts.admin')

@section('title', 'Products Report')
@section('page-title', 'Products Performance Report')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-chart-bar me-2"></i>Products Performance</span>
        <button onclick="window.print()" class="btn btn-sm btn-success">
            <i class="fas fa-print me-1"></i>Print Report
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Total Orders</th>
                        <th>Total Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('storage/products/' . $product->image) }}" 
                                         alt="{{ $product->name }}"
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                                    <strong>{{ $product->name }}</strong>
                                </div>
                            </td>
                            <td><span class="badge bg-secondary">{{ ucfirst($product->category) }}</span></td>
                            <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td>
                                @if($product->stock <= 5 && $product->stock > 0)
                                    <span class="badge bg-warning">{{ $product->stock }}</span>
                                @elseif($product->stock == 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock }}</span>
                                @endif
                            </td>
                            <td>{{ $product->order_items_count }}</td>
                            <td>
                                {{ $product->orderItems->sum('quantity') }}
                            </td>
                            <td>
                                <strong>Rp {{ number_format($product->orderItems->sum('subtotal'), 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Top Products -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-trophy me-2"></i>Top 5 Best Selling Products
            </div>
            <div class="card-body">
                @foreach($products->sortByDesc('order_items_count')->take(5) as $index => $product)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="badge bg-primary me-2" style="width: 30px;">{{ $index + 1 }}</div>
                            <img src="{{ asset('storage/products/' . $product->image) }}" 
                                 alt="{{ $product->name }}"
                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $product->order_items_count }} orders</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-dollar-sign me-2"></i>Top 5 Highest Revenue Products
            </div>
            <div class="card-body">
                @foreach($products->sortByDesc(function($product) { 
                    return $product->orderItems->sum('subtotal'); 
                })->take(5) as $index => $product)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center">
                            <div class="badge bg-success me-2" style="width: 30px;">{{ $index + 1 }}</div>
                            <img src="{{ asset('storage/products/' . $product->image) }}" 
                                 alt="{{ $product->name }}"
                                 style="width: 40px; height: 40px; object-fit: cover; border-radius: 5px; margin-right: 10px;">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <br>
                                <small class="text-muted">Rp {{ number_format($product->orderItems->sum('subtotal'), 0, ',', '.') }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection