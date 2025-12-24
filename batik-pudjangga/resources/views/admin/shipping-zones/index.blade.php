@extends('layouts.admin')

@section('title', 'Shipping Zones')
@section('page-title', 'Shipping Zones Management')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-truck me-2"></i>Shipping Cost by Province
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Shipping costs are organized by zones. Each province belongs to a specific zone with different rates for Regular and Express shipping.
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Province</th>
                        <th>Regular Shipping</th>
                        <th>Express Shipping</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($zones as $zone)
                        <tr>
                            <td>
                                <span class="badge bg-primary">Zone {{ $zone->zone }}</span>
                            </td>
                            <td><strong>{{ $zone->province }}</strong></td>
                            <td>Rp {{ number_format($zone->cost_regular, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($zone->cost_express, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('admin.shipping-zones.edit', $zone) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Zone Summary -->
<div class="row mt-4">
    @for($i = 1; $i <= 5; $i++)
        @php
            $zoneData = $zones->where('zone', $i)->first();
        @endphp
        @if($zoneData)
            <div class="col-md-4 mb-4">
                <div class="card" style="border-left: 4px solid var(--primary-color);">
                    <div class="card-body">
                        <h5 class="card-title">Zone {{ $i }}</h5>
                        <p class="mb-2">
                            <small class="text-muted">Regular:</small><br>
                            <strong>Rp {{ number_format($zoneData->cost_regular, 0, ',', '.') }}</strong>
                        </p>
                        <p class="mb-0">
                            <small class="text-muted">Express:</small><br>
                            <strong>Rp {{ number_format($zoneData->cost_express, 0, ',', '.') }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @endfor
</div>
@endsection
