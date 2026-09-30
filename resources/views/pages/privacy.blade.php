@extends('layouts.app')

@section('title', 'Privacy policy')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="page-head h2 mb-3">Privacy policy</h1>
        <p class="text-muted">Last updated {{ date('F j, Y') }}.</p>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">What we collect</h2>
            <ul class="text-muted mb-0">
                <li>Account details: name, email address and authentication data.</li>
                <li>Enrolled device activity reported by the visible agent: calls, messages, locations, app usage, media and related records.</li>
                <li>Consent records: who consented, when, and from which IP address and browser.</li>
                <li>Billing records: invoices, payment references and uploaded proof of payment.</li>
            </ul>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">How we use it</h2>
            <p class="text-muted mb-0">
                Activity data is shown only to the account that owns the device and to viewers the owner has
                explicitly shared it with. We use account and billing data to operate the service and to verify
                payments. We do not sell personal data.
            </p>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">Retention and deletion</h2>
            <p class="text-muted mb-0">
                Removing a device deletes its activity records. Account removal deletes associated devices,
                sharing and consent records. Payment proofs are retained as long as required for accounting.
            </p>
        </div>

        <div class="card p-4">
            <h2 class="h5 fw-semibold">Your choices</h2>
            <p class="text-muted mb-0">
                You can revoke sharing, suspend or remove devices at any time from your dashboard. For access,
                correction or deletion requests, contact
                {{ \App\Services\Settings::get('support_email', 'support@royaltrico.example') }}.
            </p>
        </div>

        <p class="text-muted small mt-4 mb-0">
            This page is template content for the ROYALTRICO pilot. Review it with qualified counsel before production use.
        </p>
    </div>
</div>
@endsection
