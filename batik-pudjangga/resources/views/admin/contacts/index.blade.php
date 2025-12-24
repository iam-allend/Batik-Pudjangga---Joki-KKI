@extends('layouts.admin')

@section('title', 'Messages')
@section('page-title', 'Contact Messages')

@section('content')
<div class="card">
    <div class="card-header">
        <i class="fas fa-envelope me-2"></i>All Messages
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($contacts as $contact)
                        <tr class="{{ $contact->status == 'unread' ? 'table-warning' : '' }}">
                            <td>{{ $contact->id }}</td>
                            <td><strong>{{ $contact->name }}</strong></td>
                            <td>{{ $contact->email }}</td>
                            <td>{{ Str::limit($contact->message, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ $contact->status == 'unread' ? 'warning' : 'success' }}">
                                    {{ ucfirst($contact->status) }}
                                </span>
                            </td>
                            <td>{{ $contact->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.contacts.show', $contact) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.contacts.destroy', $contact) }}" 
                                          method="POST" 
                                          onsubmit="return confirmDelete()">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
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

