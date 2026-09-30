@extends('layouts.admin')

@section('title', 'Devices')
@section('heading', 'Devices')
@section('subheading', 'All enrolled devices across every account.')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Device</th>
                    <th>Owner</th>
                    <th>OS</th>
                    <th>Source</th>
                    <th>Last seen</th>
                    <th style="min-width: 16rem;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($devices as $device)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $device->name }}</div>
                            <div class="small text-muted">{{ $device->manufacturer }} {{ $device->model }}</div>
                        </td>
                        <td>
                            @if ($device->user)
                                <a href="{{ route('admin.users.show', $device->user) }}" class="text-decoration-none">{{ $device->user->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ strtoupper($device->os) }} {{ $device->os_version }}</td>
                        <td><span class="badge text-bg-light border">{{ $device->source }}</span></td>
                        <td class="small text-muted">{{ $device->last_seen_at?->diffForHumans() ?? 'Never' }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <form method="POST" action="{{ route('admin.devices.update-status', $device) }}" class="d-flex align-items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" class="form-select form-select-sm" style="width: 8.5rem;">
                                        @foreach (['pending', 'active', 'suspended'] as $status)
                                            <option value="{{ $status }}" @selected($device->status === $status)>{{ ucfirst($status) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary">Save</button>
                                </form>
                                <form method="POST" action="{{ route('admin.devices.rotate-token', $device) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" title="Rotate agent token" onclick="return confirm('Rotate the agent token for this device?')">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No devices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($devices->hasPages())
        <div class="card-body">
            {{ $devices->links() }}
        </div>
    @endif
</div>
@endsection
