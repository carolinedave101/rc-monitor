@extends('layouts.admin')

@section('title', $user->name)
@section('heading', $user->name)
@section('subheading', $user->email)

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All accounts</a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h2 class="h6 text-muted text-uppercase mb-3">Account</h2>
            <dl class="mb-0">
                <dt class="small text-muted">Name</dt>
                <dd>{{ $user->name }}</dd>
                <dt class="small text-muted">Email</dt>
                <dd>{{ $user->email }}</dd>
                <dt class="small text-muted">Role</dt>
                <dd>
                    @if ($user->is_admin)
                        <span class="badge text-bg-dark">Admin</span>
                    @else
                        <span class="badge text-bg-light border">Customer</span>
                    @endif
                </dd>
                <dt class="small text-muted">Joined</dt>
                <dd class="mb-0">{{ $user->created_at->format('M j, Y g:i A') }}</dd>
            </dl>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">Devices</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Last seen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($user->devices as $device)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $device->name }}</div>
                                    <div class="small text-muted">{{ $device->manufacturer }} {{ $device->model }}</div>
                                </td>
                                <td>
                                    <span class="badge text-bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'suspended' ? 'danger' : 'secondary') }}">
                                        {{ ucfirst($device->status) }}
                                    </span>
                                </td>
                                <td><span class="badge text-bg-light border">{{ $device->source }}</span></td>
                                <td class="small text-muted">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No devices enrolled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Recent alerts</div>
            <div class="list-group list-group-flush">
                @forelse ($user->alerts as $alert)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <span class="fw-semibold">{{ $alert->title }}</span>
                            <span class="badge text-bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">
                                {{ ucfirst($alert->severity) }}
                            </span>
                        </div>
                        <div class="small text-muted">{{ $alert->created_at->diffForHumans() }}</div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">No alerts.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
