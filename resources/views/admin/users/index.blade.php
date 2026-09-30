@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Accounts')
@section('subheading', 'Every registered customer account.')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Role</th>
                    <th>Devices</th>
                    <th>Alerts</th>
                    <th>Joined</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $user->name }}</div>
                            <div class="small text-muted">{{ $user->email }}</div>
                        </td>
                        <td>
                            @if ($user->is_admin)
                                <span class="badge text-bg-dark">Admin</span>
                            @else
                                <span class="badge text-bg-light border">Customer</span>
                            @endif
                        </td>
                        <td>{{ $user->devices_count }}</td>
                        <td>{{ $user->alerts_count }}</td>
                        <td class="text-muted small">{{ $user->created_at->format('M j, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">Manage</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No accounts yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="card-body">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
