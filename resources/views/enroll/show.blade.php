@extends('layouts.app')

@section('title', 'Set up '.$device->name)

@section('content')
<div class="auth-shell" style="max-width: 34rem;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO" class="auth-logo mb-3">
        <h1 class="page-head h3 mb-1">Set up {{ $device->name }}</h1>
        <p class="text-muted small mb-0">Visible, consent-based monitoring — nothing hidden.</p>
    </div>

    <div class="card mb-3">
        <div class="card-body text-center">
            <div class="d-inline-block border rounded-4 p-3 bg-white mb-3">
                {!! \App\Support\QrCode::svg(route('enroll.show', $device->agent_token), 200) !!}
            </div>
            <div class="small text-muted mb-1">Scan this code with the device, or open:</div>
            <a href="{{ route('enroll.show', $device->agent_token) }}" class="small text-break">{{ route('enroll.show', $device->agent_token) }}</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white"><i class="bi bi-list-check me-1"></i> How to install</div>
        <div class="card-body">
            <ol class="mb-3 ps-3">
                <li class="mb-2">Install the ROYALTRICO agent from the official app store on this device.</li>
                <li class="mb-2">Open the agent and paste the enrollment token below.</li>
                <li class="mb-2">The agent appears in your app list, reports activity, and can be removed at any time.</li>
            </ol>
            <label class="form-label small">Enrollment token</label>
            <div class="input-group">
                <input type="text" class="form-control font-monospace" value="{{ $device->agent_token }}" readonly onclick="this.select()">
            </div>
            <div class="small text-muted mt-2">
                The agent sends this token as <code>Authorization: Bearer …</code> to the ROYALTRICO API.
            </div>
        </div>
    </div>

    <div class="alert alert-info border-0 rounded-4 small">
        <i class="bi bi-shield-lock-fill me-1"></i>
        <strong>Consent reminder.</strong> Only install this on a device you own or are explicitly authorized to
        monitor. The device owner should be aware of the installation and can remove the agent at any time.
    </div>
</div>
@endsection
