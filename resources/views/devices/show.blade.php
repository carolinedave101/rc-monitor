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
                <span class="status-dot {{ $device->isOnline() ? 'online' : 'offline' }}" id="device-status-dot"></span>
            </h1>
            <div class="text-muted small">
                {{ $device->manufacturer ?? 'Unknown' }} {{ $device->model ?? '' }} · {{ strtoupper($device->os) }}
                @if ($device->os_version)
                    v{{ $device->os_version }}
                @endif
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if ($isOwner)
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
        @else
            <span class="badge bg-info status-badge py-2 px-3">
                <i class="bi bi-people me-1"></i>Shared with you by {{ $device->user?->name ?? 'the owner' }}
            </span>
        @endif
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

<ul class="nav nav-tabs mb-4 flex-wrap" role="tablist">
    @foreach (['overview' => 'Overview', 'calls' => 'Calls', 'messages' => 'Messages', 'locations' => 'Locations', 'alerts' => 'Alerts', 'apps' => 'Apps', 'contacts' => 'Contacts', 'diagnostics' => 'Diagnostics', 'browser' => 'Browser', 'emails' => 'Email', 'media' => 'Media', 'notes' => 'Notes', 'calendar' => 'Calendar'] as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $tab === $key ? 'active' : '' }}" href="{{ route('devices.show', array_merge(['device' => $device], $tab === $key ? [] : ['tab' => $key])) }}">{{ $label }}</a>
        </li>
    @endforeach
</ul>

@if ($tab === 'overview')
    <div class="row g-3">
        <div class="col-lg-7">
            @if ($isOwner)
                <div class="card">
                    <div class="card-header bg-white"><i class="bi bi-gear me-1"></i> Setup</div>
                    <div class="card-body">
                        @if ($device->status === 'active' && $device->consent_recorded)
                            <div class="alert alert-success border-0 rounded-4 py-2 mb-3"><i class="bi bi-check2-circle me-1"></i> Delegated consent was recorded {{ $device->consented_at?->diffForHumans() }} and the device is active.</div>
                        @endif

                        <p>Install the ROYALTRICO agent on the device and configure it with this enrollment token:</p>
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-3">
                            <div class="border rounded-3 p-2 bg-white">
                                {!! \App\Support\QrCode::svg(route('enroll.show', $device->agent_token), 140) !!}
                            </div>
                            <div class="small">
                                <div class="fw-semibold mb-1">Scan to open the enrollment page</div>
                                <a href="{{ route('enroll.show', $device->agent_token) }}" target="_blank" class="text-break">{{ route('enroll.show', $device->agent_token) }}</a>
                            </div>
                        </div>
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
                    <div class="card-header bg-white"><i class="bi bi-people me-1"></i> Share this device</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('shares.store', $device) }}" class="d-flex gap-2 mb-3">
                            @csrf
                            <input type="email" name="email" class="form-control" placeholder="partner@example.com" required maxlength="255">
                            <button class="btn btn-primary px-3">Invite</button>
                        </form>
                        @forelse ($shares as $share)
                            <div class="d-flex justify-content-between align-items-center small border-top py-2">
                                <span>{{ $share->viewer?->name ?? $share->email }}</span>
                                <span class="badge bg-{{ $share->status === 'accepted' ? 'success' : 'secondary' }} status-badge">{{ ucfirst($share->status) }}</span>
                            </div>
                        @empty
                            <p class="small text-muted mb-2">No one has access yet. Sharing requires the other person to accept.</p>
                        @endforelse
                        <a href="{{ route('shares.index') }}" class="small">Manage all sharing</a>
                    </div>
                </div>
            @else
                <div class="alert alert-info border-0 rounded-4">
                    <i class="bi bi-info-circle me-1"></i> You have view access shared by
                    <strong>{{ $device->user?->name ?? 'the owner' }}</strong>.
                    Only the owner can manage this device, and either side can revoke sharing at any time.
                </div>
            @endif

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
                        <dd class="col-sm-8" id="device-last-seen">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</dd>
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

@elseif ($tab === 'apps')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>App</th>
                            <th>Category</th>
                            <th class="text-end">Duration</th>
                            <th>Launched</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($appActivities as $activity)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $activity->app_name }}</div>
                                    <div class="small text-muted">{{ $activity->package ?? '—' }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark status-badge">{{ $activity->category ?? '—' }}</span></td>
                                <td class="text-end">{{ gmdate('H:i:s', $activity->duration_seconds) }}</td>
                                <td>{{ $activity->launched_at->format('M j, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No app activity recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif ($tab === 'contacts')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contacts as $contact)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $contact->name }}</td>
                                <td class="font-monospace">{{ $contact->phone_number ?? '—' }}</td>
                                <td>{{ $contact->email ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">No contacts recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif ($tab === 'diagnostics')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Battery</th>
                            <th>Charging</th>
                            <th>Storage</th>
                            <th>Network</th>
                            <th>Recorded</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($diagnostics as $diagnostic)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @php $battery = $diagnostic->battery_percent; @endphp
                                    <span class="badge bg-{{ $battery === null ? 'secondary' : ($battery < 20 ? 'danger' : ($battery < 50 ? 'warning' : 'success')) }}">
                                        {{ $battery !== null ? $battery.'%' : '—' }}
                                    </span>
                                </td>
                                <td>{{ $diagnostic->is_charging ? 'Yes' : 'No' }}</td>
                                <td>
                                    @if ($diagnostic->storage_used_mb && $diagnostic->storage_total_mb)
                                        {{ round($diagnostic->storage_used_mb / 1024, 1) }} / {{ round($diagnostic->storage_total_mb / 1024, 1) }} GB
                                    @else
                                        —
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark status-badge">{{ strtoupper($diagnostic->network ?? '—') }}</span></td>
                                <td>{{ $diagnostic->recorded_at->format('M j, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No diagnostics recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif ($tab === 'browser')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Page</th>
                            <th>URL</th>
                            <th>Visited</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($browser as $entry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $entry->title ?? $entry->domain ?? '—' }}</div>
                                    <div class="small text-muted">{{ $entry->domain }}</div>
                                </td>
                                <td class="small text-break" style="max-width: 22rem;">{{ $entry->url }}</td>
                                <td>{{ $entry->visited_at->format('M j, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">No browser history recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif ($tab === 'emails')
    <div class="card">
        <div class="card-body p-0">
            @forelse ($emails as $email)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between">
                        <div>
                            <span class="fw-semibold">{{ $email->subject ?? '(no subject)' }}</span>
                            <span class="badge bg-{{ $email->direction === 'incoming' ? 'primary' : 'success' }} status-badge ms-1">{{ ucfirst($email->direction) }}</span>
                        </div>
                        <div class="text-muted small">{{ $email->sent_at->diffForHumans() }}</div>
                    </div>
                    <div class="small text-muted mt-1">{{ $email->address }}</div>
                    @if ($email->snippet)
                        <p class="mb-0 mt-1 text-break">{{ $email->snippet }}</p>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-envelope" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">No email activity recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>

@elseif ($tab === 'media')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>File</th>
                            <th class="text-end">Size</th>
                            <th>Taken</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($media as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-{{ $item->type === 'video' ? 'primary' : 'success' }} status-badge">
                                        <i class="bi {{ $item->type === 'video' ? 'bi-camera-video' : 'bi-image' }} me-1"></i>{{ ucfirst($item->type) }}
                                    </span>
                                </td>
                                <td class="font-monospace small">{{ $item->filename }}</td>
                                <td class="text-end">{{ $item->size_mb !== null ? number_format($item->size_mb, 1).' MB' : '—' }}</td>
                                <td>{{ $item->taken_at->format('M j, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No media recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@elseif ($tab === 'notes')
    <div class="card">
        <div class="card-body p-0">
            @forelse ($notes as $note)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between">
                        <span class="fw-semibold">{{ $note->title }}</span>
                        <span class="text-muted small">{{ $note->updated_at->diffForHumans() }}</span>
                    </div>
                    @if ($note->body)
                        <p class="mb-0 mt-1 text-break">{{ $note->body }}</p>
                    @endif
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="bi bi-journal-text" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">No notes recorded yet.</p>
                </div>
            @endforelse
        </div>
    </div>

@elseif ($tab === 'calendar')
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Event</th>
                            <th>Location</th>
                            <th>Starts</th>
                            <th>Ends</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($calendar as $event)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $event->title }}</td>
                                <td>{{ $event->location ?? '—' }}</td>
                                <td>{{ $event->starts_at->format('M j, g:i A') }}</td>
                                <td>{{ $event->ends_at?->format('M j, g:i A') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-5">No calendar events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
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

(function () {
    const url = @json(route('devices.live', $device));
    const dot = document.getElementById('device-status-dot');
    const lastSeen = document.getElementById('device-last-seen');

    async function poll() {
        try {
            const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) return;
            const data = await response.json();

            dot.classList.toggle('online', data.online);
            dot.classList.toggle('offline', !data.online);
            lastSeen.textContent = data.last_seen_human;
        } catch (error) {
            // Keep the last known state when polling fails.
        }
    }

    setInterval(poll, 15000);
})();
</script>
@endpush