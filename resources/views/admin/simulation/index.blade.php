@extends('layouts.admin')

@section('title', 'Simulation')
@section('heading', 'Simulation engine')
@section('subheading', 'Generate realistic pilot activity through the same pipelines as real agent data. Everything generated is flagged as simulated.')

@section('content')
<div class="card mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="fw-semibold">Global simulation</div>
            <div class="small text-muted">When paused, the scheduled generator stops for every device.</div>
        </div>
        <form method="POST" action="{{ route('admin.simulation.settings') }}">
            @csrf
            @method('PATCH')
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="simulation_enabled" value="1"
                       id="simulation-enabled" @checked($enabled) onchange="this.form.submit()">
                <label class="form-check-label fw-semibold" for="simulation-enabled">{{ $enabled ? 'Running' : 'Paused' }}</label>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">Devices</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Owner</th>
                    <th style="min-width: 15rem;">Profile</th>
                    <th>Last tick</th>
                    <th class="text-end" style="min-width: 21rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($devices as $device)
                    @php $profile = $device->simulationProfile; @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.devices.show', $device) }}" class="fw-semibold text-decoration-none">{{ $device->name }}</a>
                            <div class="small text-muted">{{ $device->manufacturer }} {{ $device->model }}</div>
                        </td>
                        <td class="small">
                            @if ($device->user)
                                <a href="{{ route('admin.users.show', $device->user) }}" class="text-decoration-none">{{ $device->user->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST" action="{{ route('admin.simulation.profiles.update', $device) }}" class="d-flex align-items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="enabled" value="1" @checked($profile?->enabled)>
                                </div>
                                <select name="activity_level" class="form-select form-select-sm" style="width: 7.5rem;">
                                    @foreach (\App\Models\SimulationProfile::LEVELS as $level)
                                        <option value="{{ $level }}" @selected(($profile?->activity_level ?? 'normal') === $level)>{{ ucfirst($level) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-sm btn-primary">Save</button>
                            </form>
                        </td>
                        <td class="small text-muted">{{ $profile?->last_tick_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                <form method="POST" action="{{ route('admin.simulation.tick', $device) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">Tick now</button>
                                </form>
                                <form method="POST" action="{{ route('admin.simulation.backfill', $device) }}" class="d-flex align-items-center gap-1">
                                    @csrf
                                    <input type="number" name="days" value="14" min="1" max="90" class="form-control form-control-sm" style="width: 4.5rem;">
                                    <button class="btn btn-sm btn-outline-secondary">Backfill</button>
                                </form>
                                <form method="POST" action="{{ route('admin.simulation.wipe', $device) }}" onsubmit="return confirm('Remove all simulated records for this device?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Wipe</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No devices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
