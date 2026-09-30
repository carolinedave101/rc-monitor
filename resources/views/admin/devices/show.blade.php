@extends('layouts.admin')

@section('title', $device->name)
@section('heading', $device->name)
@section('subheading', ($device->manufacturer ? $device->manufacturer.' ' : '').($device->model ?? '').' · '.strtoupper($device->os).' '.$device->os_version)

@section('actions')
    <a href="{{ route('admin.devices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All devices</a>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card p-4">
            <h2 class="h6 text-muted text-uppercase mb-3">Device</h2>
            <dl class="mb-0">
                <dt class="small text-muted">Owner</dt>
                <dd>
                    @if ($device->user)
                        <a href="{{ route('admin.users.show', $device->user) }}" class="text-decoration-none">{{ $device->user->name }}</a>
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </dd>
                <dt class="small text-muted">Status</dt>
                <dd>
                    <span class="badge text-bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'suspended' ? 'danger' : 'secondary') }}">
                        {{ ucfirst($device->status) }}
                    </span>
                </dd>
                <dt class="small text-muted">Consent</dt>
                <dd>
                    @if ($device->consent_recorded)
                        <span class="badge text-bg-success">Recorded</span>
                        <div class="small text-muted mt-1">{{ $device->consented_at?->format('M j, Y g:i A') }}</div>
                    @else
                        <span class="badge text-bg-secondary">Not recorded</span>
                    @endif
                </dd>
                <dt class="small text-muted">Source</dt>
                <dd><span class="badge text-bg-light border">{{ $device->source }}</span></dd>
                <dt class="small text-muted">Last seen</dt>
                <dd>{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</dd>
                <dt class="small text-muted">Agent token</dt>
                <dd class="mb-0 d-flex align-items-center gap-2">
                    <code class="small">{{ substr($device->agent_token, 0, 8) }}…</code>
                    <form method="POST" action="{{ route('admin.devices.rotate-token', $device) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Rotate the agent token for this device?')"><i class="bi bi-arrow-repeat"></i></button>
                    </form>
                </dd>
            </dl>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Feature states on this device</span>
                <span class="small text-muted">Enabled by default</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Feature</th>
                            <th>Category</th>
                            <th class="text-end">Enabled</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($features as $feature)
                            @php $state = $states->get($feature->id); @endphp
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $feature->name }}</div>
                                    <code class="small text-muted">{{ $feature->code }}</code>
                                </td>
                                <td class="small text-muted">{{ ucfirst($feature->category) }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('admin.devices.features.update', [$device, $feature]) }}" class="d-inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input" type="checkbox" name="enabled" value="1"
                                                   @checked($state?->enabled ?? true) onchange="this.form.submit()">
                                        </div>
                                    </form>
                                    @if ($state?->last_sync_at)
                                        <div class="small text-muted">synced {{ $state->last_sync_at->diffForHumans() }}</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
