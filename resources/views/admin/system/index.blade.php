@extends('layouts.admin')

@section('title', 'System')
@section('heading', 'System')
@section('subheading', 'Health, scheduled jobs and database backups.')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header">Health</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                        <tr><th class="text-muted small">Environment</th><td><span class="badge text-bg-{{ $health['environment'] === 'production' ? 'success' : 'warning' }}">{{ $health['environment'] }}</span></td></tr>
                        <tr><th class="text-muted small">PHP</th><td>{{ $health['php'] }}</td></tr>
                        <tr><th class="text-muted small">Laravel</th><td>{{ $health['laravel'] }}</td></tr>
                        <tr><th class="text-muted small">Database</th><td>{{ $health['database'] }}</td></tr>
                        <tr><th class="text-muted small">Queue</th><td>{{ $health['queue'] }}</td></tr>
                        <tr><th class="text-muted small">Pending jobs</th><td>{{ $health['pending_jobs'] }}</td></tr>
                        <tr><th class="text-muted small">Failed jobs</th><td class="{{ $health['failed_jobs'] ? 'text-danger fw-semibold' : '' }}">{{ $health['failed_jobs'] }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="card-body border-top small text-muted">
                Scheduled tasks: <code>simulate:devices</code> every minute and <code>backup:database</code> daily at 02:00.
                In production run <code>php artisan schedule:work</code> (or a cron entry) and a queue worker if the queue connection is not <code>sync</code>.
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Backups</span>
                <form method="POST" action="{{ route('admin.system.backup') }}">
                    @csrf
                    <button class="btn btn-sm btn-primary"><i class="bi bi-download me-1"></i> Run backup now</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>File</th>
                            <th class="text-end">Size</th>
                            <th>Created</th>
                            <th class="text-end"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $file)
                            <tr>
                                <td class="font-monospace small">{{ $file['name'] }}</td>
                                <td class="text-end small">{{ number_format($file['size'] / 1024, 1) }} KB</td>
                                <td class="small text-muted">{{ \Illuminate\Support\Carbon::createFromTimestamp($file['modified'])->diffForHumans() }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.system.backup.download', $file['name']) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No backups yet. Run one now.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
