@extends('layouts.app')

@section('title', 'Alert rules')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Alert rules</h1>
        <p class="text-muted small mb-0">Get notified about keywords in messages or when a device leaves a safe zone.</p>
    </div>
    <a href="{{ route('alerts.index') }}" class="btn btn-outline-primary px-4"><i class="bi bi-bell me-1"></i> View alerts</a>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white"><i class="bi bi-plus-circle me-1 text-primary"></i> Create rule</div>
            <div class="card-body">
                <form method="POST" action="{{ route('alerts.rules.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="type" class="form-label">Trigger type</label>
                        <select class="form-select" id="type" name="type" onchange="toggleRuleFields()">
                            <option value="keyword" @selected(old('type') === 'keyword')>Keyword in a message</option>
                            <option value="geofence" @selected(old('type') === 'geofence')>Device leaves a safe zone</option>
                        </select>
                    </div>

                    <div class="mb-3" id="device-field">
                        <label for="device_id" class="form-label">Apply to</label>
                        <select class="form-select" id="device_id" name="device_id">
                            <option value="">All devices</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}" @selected(old('device_id') == $device->id)>{{ $device->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="keyword-field">
                        <label for="keyword" class="form-label">Keyword</label>
                        <input type="text" class="form-control @error('keyword') is-invalid @enderror" id="keyword" name="keyword" value="{{ old('keyword') }}" placeholder="e.g. help, money, meet me">
                        @error('keyword')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 d-none" id="geofence-field">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="any" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="e.g. 51.5074">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="any" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="e.g. -0.1278">
                            </div>
                        </div>
                        <div>
                            <label for="radius_meters" class="form-label">Radius (meters)</label>
                            <input type="number" min="50" class="form-control" id="radius_meters" name="radius_meters" value="{{ old('radius_meters', 500) }}">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="severity" class="form-label">Severity</label>
                        <select class="form-select" id="severity" name="severity">
                            <option value="info" @selected(old('severity') === 'info')>Info</option>
                            <option value="warning" @selected(old('severity') === 'warning' || !old('severity'))>Warning</option>
                            <option value="critical" @selected(old('severity') === 'critical')>Critical</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Create rule</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        @forelse ($rules as $rule)
            <div class="card mb-3 {{ $rule->enabled ? '' : 'opacity-50' }}">
                <div class="card-body d-flex justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon {{ $rule->type === 'keyword' ? 'icon-primary' : 'icon-danger' }}">
                            <i class="bi {{ $rule->type === 'keyword' ? 'bi-chat-square-text' : 'bi-geo-alt' }}"></i>
                        </span>
                        <div>
                            <div class="fw-semibold">
                                @if ($rule->type === 'keyword')
                                    Message contains "{{ $rule->keyword }}"
                                @else
                                    Leave zone at ({{ $rule->latitude }}, {{ $rule->longitude }}), {{ $rule->radius_meters }} m
                                @endif
                                <span class="badge status-badge bg-{{ $rule->severity === 'critical' ? 'danger' : ($rule->severity === 'warning' ? 'warning' : 'info') }} ms-1">{{ ucfirst($rule->severity) }}</span>
                            </div>
                            <div class="small text-muted mt-1">
                                Applies to: {{ $rule->device?->name ?? 'All devices' }} · Created {{ $rule->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('alerts.rules.toggle', $rule) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-{{ $rule->enabled ? 'warning' : 'success' }}">
                                {{ $rule->enabled ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                        <form method="POST" action="{{ route('alerts.rules.destroy', $rule) }}" onsubmit="return confirm('Delete this rule?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-sliders" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">No alert rules yet. Create one to get notified about keywords or safe zones.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleRuleFields() {
    const type = document.getElementById('type').value;
    document.getElementById('keyword-field').classList.toggle('d-none', type !== 'keyword');
    document.getElementById('geofence-field').classList.toggle('d-none', type !== 'geofence');
}
toggleRuleFields();
</script>
@endpush