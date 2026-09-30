@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Dashboard</h1>
        <p class="text-muted small mb-0">Welcome back — here's what's happening across your devices.</p>
    </div>
    <a href="{{ route('devices.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-1"></i> Enroll device</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-primary"><i class="bi bi-phone"></i></div>
            <div>
                <div class="fw-bold fs-4 lh-1 mb-1">{{ $devices->count() }}</div>
                <div class="text-muted small">Total devices</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-success"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="fw-bold fs-4 text-primary lh-1 mb-1">{{ $activeDevices }}</div>
                <div class="text-muted small">Active</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-info"><i class="bi bi-wifi"></i></div>
            <div>
                <div class="fw-bold fs-4 text-success lh-1 mb-1">{{ $onlineDevices }}</div>
                <div class="text-muted small">Online now</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-danger"><i class="bi bi-bell"></i></div>
            <div>
                <div class="fw-bold fs-4 {{ $unreadAlerts ? 'text-danger' : '' }} lh-1 mb-1">{{ $unreadAlerts }}</div>
                <div class="text-muted small">Unread alerts</div>
            </div>
        </div></div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white">Devices</div>
            <div class="card-body p-0">
                @forelse ($devices as $device)
                    <div class="d-flex justify-content-between align-items-center border-bottom p-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="os-avatar {{ $device->os === 'ios' ? 'icon-primary' : 'icon-success' }}">
                                <i class="bi {{ $device->os === 'ios' ? 'bi-apple' : 'bi-android2' }}"></i>
                            </span>
                            <div>
                                <span class="fw-semibold">{{ $device->name }}</span>
                                <span class="status-dot {{ $device->isOnline() ? 'online' : 'offline' }} ms-2"></span>
                                <div class="small text-muted">
                                    {{ $device->manufacturer ?? 'Unknown' }} {{ $device->model ?? '' }} · {{ strtoupper($device->os) }}
                                    <span class="badge bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'suspended' ? 'danger' : 'secondary') }} status-badge ms-1">{{ ucfirst($device->status) }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('devices.show', $device) }}" class="btn btn-sm btn-outline-primary">View <i class="bi bi-arrow-right"></i></a>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-phone" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">No devices yet.</p>
                        <a href="{{ route('devices.create') }}" class="btn btn-primary btn-sm mt-2">Enroll your first device</a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-bell-fill text-primary me-1"></i> Recent alerts</span>
                <a href="{{ route('alerts.index') }}" class="small">View all</a>
            </div>
            <div class="card-body p-0">
                @forelse ($recentAlerts as $alert)
                    <div class="border-bottom p-3">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">{{ $alert->title }}</span>
                            <span class="badge status-badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($alert->severity) }}
                            </span>
                        </div>
                        <div class="small text-muted mt-1">
                            {{ $alert->device?->name ?? 'All devices' }} · {{ $alert->created_at->diffForHumans() }}
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-bell" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">No alerts yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-grid-3x3-gap text-primary me-1"></i> Service status</span>
        <span class="small text-muted">What's active on your account right now</span>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @forelse ($features as $feature)
                @php
                    $badge = match ($feature->status) {
                        'live' => 'text-bg-success',
                        'simulated' => 'text-bg-primary',
                        'beta' => 'text-bg-info',
                        default => 'text-bg-light border',
                    };
                    $label = match ($feature->status) {
                        'live' => 'Active',
                        'simulated' => 'Simulated',
                        'beta' => 'Beta',
                        default => 'Coming soon',
                    };
                @endphp
                <span class="badge {{ $badge }} py-2 px-3">{{ $feature->name }} · {{ $label }}</span>
            @empty
                <span class="text-muted small">No services published yet.</span>
            @endforelse
        </div>
    </div>
</div>
@endsection