@extends('layouts.app')

@section('title', 'Sharing')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Sharing</h1>
        <p class="text-muted small mb-0">Consent-based access to devices, revocable by either side at any time.</p>
    </div>
</div>

@if ($invitations->isNotEmpty())
    <div class="card mb-4 border-start border-4 border-primary">
        <div class="card-header bg-white"><i class="bi bi-envelope-open me-1"></i> Invitations waiting for you</div>
        <div class="list-group list-group-flush">
            @foreach ($invitations as $share)
                <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">{{ $share->device?->name ?? 'Device' }}</div>
                        <div class="small text-muted">
                            Invited by {{ $share->owner?->name ?? 'the owner' }} · {{ $share->device?->manufacturer }} {{ $share->device?->model }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('shares.accept', $share) }}">
                            @csrf
                            <button class="btn btn-sm btn-primary px-3">Accept & record consent</button>
                        </form>
                        <form method="POST" action="{{ route('shares.revoke', $share) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Decline</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="bi bi-people me-1"></i> Shared with you</div>
            <div class="list-group list-group-flush">
                @forelse ($sharedWithMe as $share)
                    <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-semibold">{{ $share->device?->name ?? 'Device' }}</div>
                            <div class="small text-muted">Owner: {{ $share->owner?->name ?? '—' }} · accepted {{ $share->accepted_at?->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex gap-2">
                            @if ($share->device)
                                <a href="{{ route('devices.show', $share->device) }}" class="btn btn-sm btn-outline-primary">View</a>
                            @endif
                            <form method="POST" action="{{ route('shares.revoke', $share) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger">Revoke</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">No devices are shared with you.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="bi bi-send me-1"></i> Sharing you manage</div>
            <div class="list-group list-group-flush">
                @forelse ($outgoing as $share)
                    <div class="list-group-item d-flex justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-semibold">{{ $share->device?->name ?? 'Device' }}</div>
                            <div class="small text-muted">
                                {{ $share->viewer?->name ?? $share->email }} ·
                                <span class="badge bg-{{ $share->status === 'accepted' ? 'success' : 'secondary' }} status-badge">{{ ucfirst($share->status) }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('shares.revoke', $share) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger">Revoke</button>
                        </form>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">
                        You haven't shared any devices yet. Open a device and invite someone from the Overview tab.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
