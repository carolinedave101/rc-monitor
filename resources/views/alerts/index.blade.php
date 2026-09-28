@extends('layouts.app')

@section('title', 'Alerts')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Alerts</h1>
        <p class="text-muted small mb-0">Keyword and geofence alerts from your enrolled devices.</p>
    </div>
    <a href="{{ route('alerts.rules') }}" class="btn btn-outline-primary px-4"><i class="bi bi-sliders me-1"></i> Manage rules</a>
</div>

@forelse ($alerts as $alert)
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body d-flex gap-3">
            <span class="stat-icon {{ $alert->severity === 'critical' ? 'icon-danger' : ($alert->severity === 'warning' ? 'icon-warning' : 'icon-info') }}">
                <i class="bi {{ $alert->severity === 'critical' ? 'bi-exclamation-triangle-fill' : ($alert->severity === 'warning' ? 'bi-warning' : 'bi-info-circle') }}"></i>
            </span>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                    <div class="fw-semibold">{{ $alert->title }}</div>
                    <span class="badge status-badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">{{ ucfirst($alert->severity) }}</span>
                </div>
                <div class="small text-muted mt-1">
                    <i class="bi bi-phone me-1"></i>{{ $alert->device?->name ?? 'All devices' }} · {{ $alert->created_at->format('M j, Y g:i A') }} ({{ $alert->created_at->diffForHumans() }})
                </div>
                <p class="mb-0 mt-2 text-break">{{ $alert->body }}</p>
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
            <p class="mt-2 mb-0">You're all caught up — no alerts.</p>
        </div>
    </div>
@endforelse

<div class="mt-4">
    {{ $alerts->links() }}
</div>
@endsection