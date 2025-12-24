@extends('layouts.admin')

@section('title', 'Message Details')
@section('page-title', 'Message Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-envelope me-2"></i>Message from {{ $contact->name }}</span>
                <span class="badge bg-{{ $contact->status == 'unread' ? 'warning' : 'success' }}">
                    {{ ucfirst($contact->status) }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <p><strong>From:</strong> {{ $contact->name }}</p>
                    <p><strong>Email:</strong> <a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
                    <p><strong>Date:</strong> {{ $contact->created_at->format('d M Y H:i') }}</p>
                </div>
                
                <hr>
                
                <div class="mb-4">
                    <h6>Message:</h6>
                    <p style="white-space: pre-wrap;">{{ $contact->message }}</p>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="mailto:{{ $contact->email }}" class="btn btn-primary">
                        <i class="fas fa-reply me-1"></i>Reply via Email
                    </a>
                    @if($contact->status == 'unread')
                        <form action="{{ route('admin.contacts.mark-read', $contact) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-1"></i>Mark as Read
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('admin.contacts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Messages
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection