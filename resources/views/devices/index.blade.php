@extends('layouts.app')

@section('title', 'Devices')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Devices</h1>
        <p class="text-muted small mb-0">
            {{ $ownDevices->count() }} device{{ $ownDevices->count() === 1 ? '' : 's' }} you manage
            @if ($sharedDevices->count())
                · {{ $sharedDevices->count() }} shared with you
            @endif
            · <span class="text-success">Consent recorded for each</span>
        </p>
    </div>
    <a href="{{ route('devices.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-1"></i> Enroll device</a>
</div>

<h2 class="h6 text-uppercase text-muted mb-3">Your devices</h2>

@forelse ($ownDevices as $device)
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="os-avatar {{ $device->os === 'ios' ? 'icon-primary' : 'icon-success' }}">
                    <i class="bi {{ $device->os === 'ios' ? 'bi-apple' : 'bi-android2' }}"></i>
                </span>
                <div>
                <div class="fw-semibold fs-5">
                    {{ $device->name }}
                    <span class="status-dot {{ $device->isOnline() ? 'online' : 'offline' }} ms-2"></span>
                </div>
                <div class="text-muted">
                    {{ $device->manufacturer ?? 'Unknown' }} {{ $device->model ?? '' }} · {{ strtoupper($device->os) }}
                    @if ($device->os_version)
                        v{{ $device->os_version }}
                    @endif
                </div>
                <div class="mt-2">
                    <span class="badge bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'suspended' ? 'danger' : 'secondary') }} status-badge">{{ ucfirst($device->status) }}</span>
                    @if ($device->consent_recorded)
                        <span class="badge bg-info status-badge"><i class="bi bi-check2-circle me-1"></i>Consented</span>
                    @else
                        <span class="badge bg-warning text-dark status-badge">Consent pending</span>
                    @endif
                    <span class="badge bg-light text-dark status-badge"><i class="bi bi-clock-history me-1"></i>Last seen {{ $device->last_seen_at?->diffForHumans() ?? 'never' }}</span>
                </div>
                </div>
            </div>
            <a href="{{ route('devices.show', $device) }}" class="btn btn-outline-primary btn-sm">View <i class="bi bi-arrow-right"></i></a>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-phone" style="font-size: 3rem;"></i>
            <p class="mt-2 mb-0">No devices yet.</p>
            <a href="{{ route('devices.create') }}" class="btn btn-primary btn-sm mt-2">Enroll your first device</a>
        </div>
    </div>
@endforelse

@if ($sharedDevices->isNotEmpty())
    <h2 class="h6 text-uppercase text-muted mt-4 mb-3">Shared with you</h2>

    @foreach ($sharedDevices as $device)
        <div class="card mb-3 border-start border-4 border-info">
            <div class="card-body d-flex justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="os-avatar {{ $device->os === 'ios' ? 'icon-primary' : 'icon-success' }}">
                        <i class="bi {{ $device->os === 'ios' ? 'bi-apple' : 'bi-android2' }}"></i>
                    </span>
                    <div>
                        <div class="fw-semibold fs-5">
                            {{ $device->name }}
                            <span class="status-dot {{ $device->isOnline() ? 'online' : 'offline' }} ms-2"></span>
                        </div>
                        <div class="text-muted">
                            Shared by {{ $device->user?->name ?? 'the owner' }} · {{ strtoupper($device->os) }}
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-info status-badge"><i class="bi bi-people me-1"></i>Shared with you</span>
                            <span class="badge bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'suspended' ? 'danger' : 'secondary') }} status-badge">{{ ucfirst($device->status) }}</span>
                            <span class="badge bg-light text-dark status-badge"><i class="bi bi-clock-history me-1"></i>Last seen {{ $device->last_seen_at?->diffForHumans() ?? 'never' }}</span>
                        </div>
                    </div>
                </div>
                <a href="{{ route('devices.show', $device) }}" class="btn btn-outline-primary btn-sm">View <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    @endforeach
@endif
@endsection
