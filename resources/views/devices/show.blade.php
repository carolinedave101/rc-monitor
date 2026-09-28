@extends('layouts.app')

@section('title', $device->name)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <span class="os-avatar {{ $device->os === 'ios' ? 'icon-primary' : 'icon-success' }}">
            <i class="bi {{ $device->os === 'ios' ? 'bi-apple' : 'bi-android2' }}"></i>
        </span>
        <div>
            <h1 class="page-head h3 mb-0">
                {{ $device->name }}
                <span class="status-dot {{ $device->isOnline() ? 'online' : 'offline' }}"></span>
            </h1>
            <div class="text-muted small">
                {{ $device->manufacturer ?? 'Unknown' }} {{ $device->model ?? '' }} · {{ strtoupper($device->os) }}
                @if ($device->os_version)
                    v{{ $device->os_version }}
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2">
        @if ($device->status === 'active')
            <form method="POST" action="{{ route('devices.status', $device) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="suspended">
                <button class="btn btn-outline-danger btn-sm"><i class="bi bi-pause-circle"></i> Suspend</button>
            </form>
        @elseif ($device->status === 'suspended')
            <form method="POST" action="{{ route('devices.status', $device) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="active">
                <button class="btn btn-outline-success btn-sm"><i class="bi bi-play-circle"></i> Activate</button>
            </form>
        @else
            <form method="POST" action="{{ route('devices.consent', $device) }}">
                @csrf
                <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle"></i> Record consent & activate</button>
            </form>
        @endif
        <form method="POST" action="{{ route('devices.destroy', $device) }}" onsubmit="return confirm('Remove this device and all of its data? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash"></i> Remove</button>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-primary"><i class="bi bi-telephone"></i></div>
            <div>
                <div class="fw-bold fs-4 lh-1 mb-1">{{ $calls->count() }}</div>
                <div class="text-muted small">Recent calls</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-success"><i class="bi bi-chat-dots"></i></div>
            <div>
                <div class="fw-bold fs-4 lh-1 mb-1">{{ $messages->count() }}</div>
                <div class="text-muted small">Recent messages</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-info"><i class="bi bi-geo-alt"></i></div>
            <div>
                <div class="fw-bold fs-4 lh-1 mb-1">{{ $locations->count() }}</div>
                <div class="text-muted small">Location points</div>
            </div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card stat-card"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon icon-danger"><i class="bi bi-bell"></i></div>
            <div>
                <div class="fw-bold fs-4 {{ $alerts->count() ? 'text-danger' : '' }} lh-1 mb-1">{{ $alerts->count() }}</div>
                <div class="text-muted small">Alerts</div>
            </div>
        </div></div>
    </div>
</div>

<ul class="nav nav-tabs mb-4" role="tablist">
    @foreach (['overview' => 'Overview', 'calls' => 'Calls', 'messages' => 'Messages', 'locations' => 'Locations', 'alerts' => 'Alerts'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('devices.show', array_merge(['device' => $device], $tab === $key ? [] : ['tab' => $key])) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

@if ($tab === 'overview')
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header bg-white"><i class="bi bi-gear me-1"></i> Setup</div>
                <div class="card-body">
                    @if ($device->status === 'active' && $device->consent_recorded)
                        <div class="alert alert-success border-0 rounded-4 py-2 mb-3"><i class="bi bi-check2-circle me-1"></i> Delegated consent was recorded {{ $device->consented_at?->diffForHumans() }} and the device is active.</div>
                    @endif

                    <p>Install the ROYALTRICO agent on the device and configure it with this enrollment token:</p>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control font-monospace" id="agent-token" value="{{ $device->agent_token }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToken()"><i class="bi bi-clipboard"></i></button>
                    </div>
                    <p class="text-muted small mb-0">Send it to the agent:
                        <code>Authorization: Bearer {{ $device->agent_token }}</code> to
                        <code>POST /api/agent/heartbeat</code> and <code>POST /api/agent/ingest</code>.</p>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-white"><i class="bi bi-info-circle me-1"></i> Details</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $device->status === 'active' ? 'success' : ($device->status === 'suspended' ? 'danger' : 'secondary') }}">{{ ucfirst($device->status) }}</span>
                        </dd>
                        <dt class="col-sm-4">OS</dt>
                        <dd class="col-sm-8">{{ ucfirst($device->os) }}{{ $device->os_version ? ' '.$device->os_version : '' }}</dd>
                        <dt class="col-sm-4">Phone number</dt>
                        <dd class="col-sm-8">{{ $device->phone_number ?? '—' }}</dd>
                        <dt class="col-sm-4">Consent</dt>
                        <dd class="col-sm-8">
                            {{ $device->consent_recorded ? 'Recorded '.$device->consented_at?->diffForHumans() : 'Not yet recorded' }}
                        </dd>
                        <dt class="col-sm-4">Last seen</dt>
                        <dd class="col-sm-8">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</dd>
                        <dt class="col-sm-4">Enrolled</dt>
                        <dd class="col-sm-8">{{ $device->created_at->diffForHumans() }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span>Recent alerts</span>
                    <a href="{{ route('devices.show', ['device' => $device, 'tab' => 'alerts']) }}" class="small">All</a>
                </div>
                <div class="card-body p-0">
                    @forelse ($alerts as $alert)
                        <div class="border-bottom p-3">
                            <div class="d-flex justify-content-between">
                                <span class="fw-semibold">{{ $alert->title }}</span>
                                <span class="badge status-badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">{{ ucfirst($alert->severity) }}</span>
                            </div>
                            <div class="small text-muted mt-1">{{ $alert->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-bell" style="font-size: 3rem;"></i>
                            <p class="mt-2 mb-0">No alerts.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

@elseif ($tab === 'calls')
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Direction</th>
                        <th>Contact</th>
                        <th>Number</th>
                        <th class="text-end">Duration</th>
                        <th>Started</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($calls as $call)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($call->direction === 'incoming')
                                    <span class="badge bg-primary"><i class="bi bi-arrow-down-left"></i> Incoming</span>
                                @elseif ($call->direction === 'outgoing')
                                    <span class="badge bg-success"><i class="bi bi-arrow-up-right"></i> Outgoing</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x"></i> Missed</span>
                                @endif
                            </td>
                            <td>{{ $call->contact_name ?? '—' }}</td>
                            <td class="font-monospace">{{ $call->phone_number ?? '—' }}</td>
                            <td class="text-end">
                                @if ($call->duration_seconds !== null)
                                    {{ floor($call->duration_seconds / 60) }}:{{ str_pad($call->duration_seconds % 60, 2, '0', STR_PAD_LEFT) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $call->started_at->format('M j, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No calls recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="text-muted small mt-2">Showing the most recent {{ $calls->count() }} of {{ $device->calls()->count() }} calls.</p>

@elseif ($tab === 'messages')
    <div class="card">
        <div class="card-body p-0">
            @forelse ($messages as $message)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="fw-semibold">{{ $message->contact_name ?? $message->phone_number ?? 'Unknown' }}</span>
                            <span class="badge bg-{{ $message->direction === 'incoming' ? 'primary' : 'success' }} status-badge ms-1">{{ ucfirst($message->direction) }}</span>
                            @if ($message->was_deleted)
                                <span class="badge bg-danger status-badge">Deleted</span>
                            @endif
                        </div>
                        <div class="text-muted small">
                            {{ $message->platform ? strtoupper($message->platform) : 'SMS' }} · {{ $message->sent_at->diffForHumans() }}
                        </div>
                    </div>
                    <p class="mb-0 mt-2 text-break">{{ $message->body }}</p>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">No messages recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>

    @if ($messagesPlatforms->count())
        <div class="mt-3">
            @foreach ($messagesPlatforms as $platform => $platformMessages)
                <span class="badge bg-light text-dark me-1 status-badge">{{ $platform ? strtoupper($platform) : 'SMS' }}: {{ $platformMessages->count() }}</span>
            @endforeach
        </div>
    @endif

@elseif ($tab === 'locations')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Coordinates</th>
                            <th>Accuracy</th>
                            <th>Label</th>
                            <th>Recorded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($locations as $location)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="font-monospace">{{ $location->latitude }}, {{ $location->longitude }}</td>
                                <td>{{ $location->accuracy_meters ? round($location->accuracy_meters).' m' : '—' }}</td>
                                <td>{{ $location->label ?? '—' }}</td>
                                <td>{{ $location->recorded_at->format('M j, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No location points recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif ($tab === 'alerts')
    <div class="card">
        <div class="card-body p-0">
            @forelse ($alerts as $alert)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">{{ $alert->title }}</span>
                        <span class="badge status-badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'warning' ? 'warning' : 'info') }}">{{ ucfirst($alert->severity) }}</span>
                    </div>
                    <div class="small text-muted mt-1">{{ $alert->created_at->format('M j, g:i A') }}</div>
                    <p class="mb-0 mt-1 text-break">{{ $alert->body }}</p>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-bell" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">No alerts for this device.</p>
                </div>
            @endforelse
        </div>
    </div>
@endif
@endsection

@push('scripts')
<script>
function copyToken() {
    const el = document.getElementById('agent-token');
    el.select();
    document.execCommand('copy');
}
</script>
@endpush