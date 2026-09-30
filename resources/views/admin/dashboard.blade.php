@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Admin dashboard')
@section('subheading', 'Overview of accounts, devices and alerts.')

@section('content')
<div class="row g-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-primary"><i class="bi bi-people"></i></span>
                <div>
                    <div class="text-muted small">Accounts</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['users']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-success"><i class="bi bi-phone"></i></span>
                <div>
                    <div class="text-muted small">Devices</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['devices']) }}</div>
                    <div class="small text-muted">{{ number_format($stats['online_devices']) }} online now</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-warning"><i class="bi bi-bell"></i></span>
                <div>
                    <div class="text-muted small">Alerts</div>
                    <div class="h4 fw-bold mb-0">{{ number_format($stats['alerts']) }}</div>
                    <div class="small text-muted">{{ number_format($stats['unread_alerts']) }} unread</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 p-4">
            <div class="d-flex align-items-center gap-3">
                <span class="stat-icon icon-info"><i class="bi bi-shield-check"></i></span>
                <div>
                    <div class="text-muted small">Admin access</div>
                    <div class="h4 fw-bold mb-0">Enabled</div>
                    <div class="small text-muted">Audit trail active</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
