@extends('layouts.admin')

@section('title', 'Edit Shipping Zone')
@section('page-title', 'Edit Shipping Zone')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit me-2"></i>Edit Shipping Cost for {{ $shippingZone->province }}
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Province:</strong> {{ $shippingZone->province }}<br>
                    <strong>Zone:</strong> {{ $shippingZone->zone }}
                </div>
                
                <form action="{{ route('admin.shipping-zones.update', $shippingZone) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Regular Shipping Cost (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('cost_regular') is-invalid @enderror" 
                                   name="cost_regular" value="{{ old('cost_regular', $shippingZone->cost_regular) }}" 
                                   min="0" required>
                            @error('cost_regular')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Standard delivery time: 3-5 days</small>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Express Shipping Cost (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('cost_express') is-invalid @enderror" 
                                   name="cost_express" value="{{ old('cost_express', $shippingZone->cost_express) }}" 
                                   min="0" required>
                            @error('cost_express')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Fast delivery time: 1-2 days</small>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Shipping Cost
                        </button>
                        <a href="{{ route('admin.shipping-zones.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Other Provinces in Same Zone -->
        <div class="card mt-4">
            <div class="card-header">
                <i class="fas fa-info-circle me-2"></i>Other Provinces in Zone {{ $shippingZone->zone }}
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach(\App\Models\ShippingZone::where('zone', $shippingZone->zone)->get() as $zone)
                        <div class="col-md-6 mb-2">
                            <span class="badge bg-light text-dark">{{ $zone->province }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection