@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Admin dashboard')
@section('subheading', 'Overview of accounts, devices and alerts.')

@section('content')
<div class="row g-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-primary"><i class="bi bi-people"></i></span>
                <div>
                    <div class="text-muted small">Accounts</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['users']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-success"><i class="bi bi-phone"></i></span>
                <div>
                    <div class="text-muted small">Devices</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['devices']) }}</div>
                    <div class="small text-muted">{{ number_format($stats['online_devices']) }} online now</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-warning"><i class="bi bi-bell"></i></span>
                <div>
                    <div class="text-muted small">Alerts</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['alerts']) }}</div>
                    <div class="small text-muted">{{ number_format($stats['unread_alerts']) }} unread</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-info"><i class="bi bi-shield-check"></i></span>
                <div>
                    <div class="text-muted small">Admin access</div>
                    <div class="h4 fw-bold mb-0">Enabled</div>
                    <div class="small text-muted">Audit trail active</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.payments.index') }}" class="text-decoration-none">
            <div class="card h-100 p-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon icon-warning"><i class="bi bi-cash-coin"></i></span>
                    <div>
                        <div class="text-muted small">Payments to verify</div>
                        <div class="h4 fw-bold mb-0 text-body">{{ number_format($stats['pending_payments']) }}</div>
                        <div class="small text-muted">Open the queue</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('admin.invoices.index') }}" class="text-decoration-none">
            <div class="card h-100 p-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon icon-danger"><i class="bi bi-receipt"></i></span>
                    <div>
                        <div class="text-muted small">Outstanding invoices</div>
                        <div class="h4 fw-bold mb-0 text-body">{{ number_format($stats['outstanding_invoices']) }}</div>
                        <div class="small text-muted">Unpaid or under review</div>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="bi bi-activity me-1"></i> Platform activity — last 14 days</span>
        <span class="small text-muted">Calls, messages and alerts across all devices</span>
    </div>
    <div class="card-body">
        <div class="chart-wrap">
            <canvas id="admin-activity-chart"></canvas>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>Recent admin activity</span>
        <span class="badge text-bg-light">{{ $recentActivity->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>When</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Subject</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentActivity as $log)
                    <tr>
                        <td class="text-muted small">{{ $log->created_at->diffForHumans() }}</td>
                        <td>{{ $log->user?->name ?? 'System' }}</td>
                        <td><code class="small">{{ $log->action }}</code></td>
                        <td class="text-muted small">
                            {{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-4">No admin activity yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    const canvas = document.getElementById('admin-activity-chart');
    if (!canvas || !window.Chart) return;

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: @json($activityLabels),
            datasets: [
                {
                    label: 'Messages',
                    data: @json($activityMessages),
                    borderColor: '#1b6ff5',
                    backgroundColor: 'rgba(27, 111, 245, .10)',
                    fill: true,
                    tension: .35,
                    pointRadius: 2,
                },
                {
                    label: 'Calls',
                    data: @json($activityCalls),
                    borderColor: '#0d3bbf',
                    backgroundColor: 'rgba(13, 59, 191, .08)',
                    fill: true,
                    tension: .35,
                    pointRadius: 2,
                },
                {
                    label: 'Alerts',
                    data: @json($activityAlerts),
                    borderColor: '#dc3d4f',
                    backgroundColor: 'rgba(220, 61, 79, .08)',
                    fill: true,
                    tension: .35,
                    pointRadius: 2,
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
})();
</script>
@endpush
