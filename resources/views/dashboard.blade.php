@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Dashboard</h1>
        <p class="text-muted small mb-0">Welcome back — here's what's happening across your devices. <span class="live-pulse me-1"></span><span id="live-updated" class="text-muted">Live</span></p>
    </div>
    <a href="{{ route('devices.create') }}" class="btn btn-primary px-4"><i class="bi bi-plus-lg me-1"></i> Enroll device</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-primary"><i class="bi bi-phone"></i></div>
            <div>
                <div class="fw-bold fs-4 lh-1 mb-1" id="stat-total">{{ $devices->count() }}</div>
                <div class="text-muted small">Total devices</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-success"><i class="bi bi-shield-check"></i></div>
            <div>
                <div class="fw-bold fs-4 text-primary lh-1 mb-1" id="stat-active">{{ $activeDevices }}</div>
                <div class="text-muted small">Active</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-info"><i class="bi bi-wifi"></i></div>
            <div>
                <div class="fw-bold fs-4 text-success lh-1 mb-1" id="stat-online">{{ $onlineDevices }}</div>
                <div class="text-muted small">Online now</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-danger"><i class="bi bi-bell"></i></div>
            <div>
                <div class="fw-bold fs-4 {{ $unreadAlerts ? 'text-danger' : '' }} lh-1 mb-1" id="stat-unread">{{ $unreadAlerts }}</div>
                <div class="text-muted small">Unread alerts</div>
            </div>
        </div></div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span><i class="bi bi-activity text-primary me-1"></i> Activity — last 7 days</span>
        <span class="small text-muted">Calls and messages across your devices</span>
    </div>
    <div class="card-body">
        <div class="chart-wrap">
            <canvas id="activity-chart"></canvas>
        </div>
    </div>
</div>

@if ($steps->isNotEmpty())
    <div class="card mb-4">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span><i class="bi bi-signpost-split text-primary me-1"></i> Your service plan</span>
            <a href="{{ route('journey.index') }}" class="small">View plan</a>
        </div>
        <div class="card-body">
            @if ($pausedStep)
                <div class="alert alert-warning d-flex gap-2 align-items-start mb-3">
                    <i class="bi bi-pause-circle-fill"></i>
                    <div>
                        <strong>Your plan is paused.</strong>
                        @if ($pausedStep->paused_reason)
                            <div class="small mt-1">{{ $pausedStep->paused_reason }}</div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>{{ $completedSteps }} of {{ $steps->count() }} steps complete</span>
                <span>{{ $progress }}%</span>
            </div>
            <div class="progress mb-3" style="height: .5rem;">
                <div class="progress-bar" style="width: {{ $progress }}%"></div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @foreach ($steps as $step)
                    <span class="badge text-bg-{{ $step->isPaused() ? 'dark' : $step->statusColor() }} py-2 px-3">
                        {{ $step->position }}. {{ $step->title }} · {{ $step->isPaused() ? 'Paused' : $step->statusLabel() }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>
@endif

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
                                <span class="status-dot {{ $device->isOnline() ? 'online' : 'offline' }} ms-2" data-device-dot="{{ $device->id }}"></span>
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
            <div class="card-body p-0" id="recent-alerts-list">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    const url = @json(route('dashboard.live'));
    const timeFormatter = new Intl.DateTimeFormat([], { hour: 'numeric', minute: '2-digit', second: '2-digit' });
    const severityClass = { critical: 'danger', warning: 'warning', info: 'info' };

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function renderAlerts(alerts) {
        const list = document.getElementById('recent-alerts-list');
        if (!list || !Array.isArray(alerts)) return;

        if (alerts.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-5"><i class="bi bi-bell" style="font-size: 3rem;"></i><p class="mt-2 mb-0">No alerts yet.</p></div>';
            return;
        }

        list.innerHTML = alerts.map(function (alert) {
            const severity = severityClass[alert.severity] || 'info';
            const label = alert.severity ? alert.severity.charAt(0).toUpperCase() + alert.severity.slice(1) : '';
            return '<div class="border-bottom p-3">'
                + '<div class="d-flex justify-content-between">'
                + '<span class="fw-semibold">' + escapeHtml(alert.title) + '</span>'
                + '<span class="badge status-badge bg-' + severity + '">' + escapeHtml(label) + '</span>'
                + '</div>'
                + '<div class="small text-muted mt-1">' + escapeHtml(alert.device) + ' · ' + escapeHtml(alert.created_at_human) + '</div>'
                + '</div>';
        }).join('');
    }

    const canvas = document.getElementById('activity-chart');
    if (canvas && window.Chart) {
        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: @json($activityLabels),
                datasets: [
                    {
                        label: 'Messages',
                        data: @json($activityMessages),
                        backgroundColor: 'rgba(27, 111, 245, .7)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Calls',
                        data: @json($activityCalls),
                        backgroundColor: 'rgba(13, 59, 191, .85)',
                        borderRadius: 6,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } },
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                },
            },
        });
    }

    async function poll() {
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('HTTP ' + response.status);
            const data = await response.json();

            document.getElementById('stat-total').textContent = data.stats.total;
            document.getElementById('stat-active').textContent = data.stats.active;
            document.getElementById('stat-online').textContent = data.stats.online;

            const unread = document.getElementById('stat-unread');
            unread.textContent = data.stats.unread;
            unread.classList.toggle('text-danger', data.stats.unread > 0);

            data.devices.forEach(function (device) {
                const dot = document.querySelector('[data-device-dot="' + device.id + '"]');
                if (dot) {
                    dot.classList.toggle('online', device.online);
                    dot.classList.toggle('offline', !device.online);
                }
            });

            const badge = document.getElementById('nav-unread-badge');
            if (badge) {
                badge.textContent = data.stats.notifications;
                badge.classList.toggle('d-none', data.stats.notifications === 0);
            }

            renderAlerts(data.alerts);
            document.getElementById('live-updated').textContent = 'Live · updated ' + timeFormatter.format(new Date());
        } catch (error) {
            document.getElementById('live-updated').textContent = 'Reconnecting…';
        }
    }

    poll();
    setInterval(poll, 15000);
})();
</script>
@endpush