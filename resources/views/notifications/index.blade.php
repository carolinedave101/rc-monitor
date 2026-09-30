@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Notifications</h1>
        <p class="text-muted small mb-0">Alerts, plan updates and account activity.</p>
    </div>
    @if (auth()->user()->unreadNotifications()->count())
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button class="btn btn-outline-primary px-4"><i class="bi bi-check2-all me-1"></i> Mark all as read</button>
        </form>
    @endif
</div>

@forelse ($notifications as $notification)
    @php
        $data = $notification->data;
        $isUnread = $notification->read_at === null;
    @endphp
    <div class="card mb-3 {{ $isUnread ? 'border-start border-4 border-primary' : '' }}">
        <div class="card-body d-flex gap-3">
            <span class="stat-icon {{ ($data['severity'] ?? null) === 'critical' ? 'icon-danger' : (($data['severity'] ?? null) === 'warning' ? 'icon-warning' : 'icon-info') }}">
                <i class="bi {{ ($data['type'] ?? '') === 'journey' ? 'bi-signpost-split' : 'bi-bell' }}"></i>
            </span>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                    <div class="fw-semibold">{{ $data['title'] ?? 'Notification' }}</div>
                    <div class="small text-muted">
                        {{ $notification->created_at->diffForHumans() }}
                        @if ($isUnread)
                            <span class="badge bg-primary status-badge ms-1">New</span>
                        @endif
                    </div>
                </div>
                @if (! empty($data['body']))
                    <p class="mb-2 mt-1 text-break">{{ $data['body'] }}</p>
                @endif
                <div class="d-flex gap-2">
                    @if (! empty($data['url']))
                        <a href="{{ $data['url'] }}" class="btn btn-sm btn-outline-primary">Open</a>
                    @endif
                    @if ($isUnread)
                        <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Mark read</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
            <p class="mt-2 mb-0">No notifications yet.</p>
        </div>
    </div>
@endforelse

@if ($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endif
@endsection
