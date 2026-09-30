@extends('layouts.app')

@section('title', 'About ROYALTRICO')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="page-head h2 mb-3">About ROYALTRICO</h1>
        <p class="lead text-muted">
            Consent-based device monitoring for families, couples, caregivers and employers.
        </p>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">Our approach</h2>
            <p class="text-muted mb-3">
                ROYALTRICO exists to make safety and transparency compatible. Every device is enrolled with an
                explicit, recorded consent from its owner, the agent is a visible app that can be removed at any
                time, and either side of a sharing arrangement can revoke access.
            </p>
            <p class="text-muted mb-0">
                We believe monitoring should be a conversation — not a secret. That is why consent records,
                statuses and account actions are all visible in the dashboard.
            </p>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">Who it's for</h2>
            <ul class="text-muted mb-0">
                <li><strong>Parents</strong> — a child's device, enrolled together with parental consent.</li>
                <li><strong>Couples &amp; partners</strong> — mutual or one-way location and activity sharing, revocable by either side.</li>
                <li><strong>Caregivers</strong> — an elderly parent's device, with location sharing in the open.</li>
                <li><strong>Employers</strong> — company-issued devices under a written policy.</li>
            </ul>
        </div>

        <div class="card p-4">
            <h2 class="h5 fw-semibold">Contact</h2>
            <p class="text-muted mb-0">
                <i class="bi bi-envelope me-1"></i>
                {{ \App\Services\Settings::get('support_email', 'support@royaltrico.example') }}
            </p>
        </div>

        <p class="text-muted small mt-4 mb-0">
            This page is template content for the ROYALTRICO pilot. Review it with qualified counsel before production use.
        </p>
    </div>
</div>
@endsection
