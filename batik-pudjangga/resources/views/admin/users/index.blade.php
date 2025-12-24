@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'Users Management')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-users me-2"></i>All Users
    </div>
    <div class="card-body">
        <!-- Search -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form action="{{ route('admin.users.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search users..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Orders</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 0.9rem;">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <strong>{{ $user->name }}</strong>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $user->is_admin ? 'danger' : 'primary' }}">
                                    {{ $user->is_admin ? 'Admin' : 'Customer' }}
                                </span>
                            </td>
                            <td>{{ $user->orders->count() }}</td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-sm btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id != auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" 
                                              method="POST" 
                                              onsubmit="return confirmDelete('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
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
