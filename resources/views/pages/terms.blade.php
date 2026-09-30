@extends('layouts.app')

@section('title', 'Terms of service')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <h1 class="page-head h2 mb-3">Terms of service</h1>
        <p class="text-muted">Last updated {{ date('F j, Y') }}.</p>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">The service</h2>
            <p class="text-muted mb-0">
                ROYALTRICO provides a dashboard for activity reported by a visible agent installed on enrolled
                devices. Features and their status (active, simulated, beta or coming soon) are shown in your
                account.
            </p>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">Authorized use only</h2>
            <p class="text-muted mb-0">
                You may only enroll devices you own or are explicitly authorized to monitor. Consent must be
                recorded for every device before monitoring begins. Installing monitoring software without
                authorization is illegal in many jurisdictions, and you are solely responsible for compliance.
            </p>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">Payments</h2>
            <p class="text-muted mb-0">
                Invoices are issued in your account with one or more payment methods. Payments are verified
                manually from the proof you upload; once approved, your plan or service step is updated. Contact
                support for refund or guarantee requests.
            </p>
        </div>

        <div class="card p-4 mb-4">
            <h2 class="h5 fw-semibold">Suspension</h2>
            <p class="text-muted mb-0">
                We may pause an account or service step — with a reason shown in your account — for unpaid
                invoices, suspected misuse or technical reasons.
            </p>
        </div>

        <div class="card p-4">
            <h2 class="h5 fw-semibold">Disclaimer</h2>
            <p class="text-muted mb-0">
                The service is provided "as is" without warranties. To the extent permitted by law, we are not
                liable for losses arising from use of the service or from unauthorized use.
            </p>
        </div>

        <p class="text-muted small mt-4 mb-0">
            This page is template content for the ROYALTRICO pilot. Review it with qualified counsel before production use.
        </p>
    </div>
</div>
@endsection
