@extends('layouts.admin')

@section('title', $user->name)
@section('heading', $user->name)
@section('subheading', $user->email)

@section('actions')
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All accounts</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-signpost-split me-1"></i> Service plan</span>
        <span class="badge text-bg-light border">{{ $user->serviceSteps->count() }} steps</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width: 3rem;">#</th>
                    <th>Step</th>
                    <th>Status</th>
                    <th>Payment</th>
                    <th class="text-end" style="min-width: 19rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($user->serviceSteps as $step)
                    <tr>
                        <td class="text-muted">{{ $step->position }}</td>
                        <td>
                            <div class="fw-semibold">{{ $step->title }}</div>
                            @if ($step->description)
                                <div class="small text-muted">{{ $step->description }}</div>
                            @endif
                            @if ($step->isPaused() && $step->paused_reason)
                                <div class="small text-danger mt-1"><i class="bi bi-pause-circle me-1"></i>{{ $step->paused_reason }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="badge text-bg-{{ $step->isPaused() ? 'dark' : $step->statusColor() }}">
                                {{ $step->isPaused() ? 'Paused' : $step->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            @if ($step->requires_payment)
                                <span class="badge text-bg-warning">Required</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                @if (! $step->isCompleted() && ! $step->isPaused())
                                    @if ($step->status === 'pending')
                                        <form method="POST" action="{{ route('admin.steps.start', $step) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary">Start</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.steps.advance', $step) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success">Complete &amp; continue</button>
                                        </form>
                                    @endif

                                    <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#pause-step-{{ $step->id }}">Pause</button>
                                @endif

                                @if ($step->isPaused())
                                    <form method="POST" action="{{ route('admin.steps.resume', $step) }}">
                                        @csrf
                                        <button class="btn btn-sm btn-primary">Resume</button>
                                    </form>
                                @endif

                                @if (! $step->isCompleted())
                                    <form method="POST" action="{{ route('admin.steps.destroy', $step) }}" onsubmit="return confirm('Remove this step from the plan?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
                            </div>

                            @if (! $step->isCompleted() && ! $step->isPaused())
                                <div class="modal fade" id="pause-step-{{ $step->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <form method="POST" action="{{ route('admin.steps.pause', $step) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Pause "{{ $step->title }}"</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason (shown to the customer)</label>
                                                        <textarea name="reason" class="form-control" rows="3" required maxlength="1000"></textarea>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="suspend_services" value="1" id="suspend-{{ $step->id }}">
                                                        <label class="form-check-label small" for="suspend-{{ $step->id }}">
                                                            Also suspend this customer's devices while paused
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button class="btn btn-warning">Pause plan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No plan steps yet. Add the first step below.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">
        <form method="POST" action="{{ route('admin.users.steps.store', $user) }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label small">New step title</label>
                <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Review first week of activity" required maxlength="255">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Description (optional)</label>
                <input type="text" name="description" class="form-control form-control-sm" maxlength="1000">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="requires_payment" value="1" id="requires-payment">
                    <label class="form-check-label small" for="requires-payment">Requires payment</label>
                </div>
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-primary w-100">Add step</button>
            </div>
        </form>
    </div>
</div>

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
