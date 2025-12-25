@extends('layouts.app')

@section('title', 'My Addresses')

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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Addresses</h5>
                    <a href="{{ route('address.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Address
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($addresses->count() > 0)
                        <div class="row">
                            @foreach($addresses as $address)
                            <div class="col-md-6 mb-3">
                                <div class="card {{ $address->is_default ? 'border-primary' : '' }}">
                                    <div class="card-body">
                                        @if($address->is_default)
                                            <span class="badge bg-primary mb-2">Default Address</span>
                                        @endif
                                        
                                        <h6>{{ $address->recipient_name }}</h6>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-phone me-1"></i> {{ $address->phone }}
                                        </p>
                                        <p class="mb-2">
                                            {{ $address->address }}<br>
                                            {{ $address->city }}, {{ $address->province }}<br>
                                            {{ $address->postal_code }}
                                        </p>

                                        <div class="d-flex gap-2">
                                            <a href="{{ route('address.edit', $address) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            
                                            @if(!$address->is_default)
                                                <form action="{{ route('address.set.default', $address) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        <i class="fas fa-check"></i> Set Default
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('address.destroy', $address) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this address?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No addresses saved yet.</p>
                            <a href="{{ route('address.create') }}" class="btn btn-primary">Add Your First Address</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
