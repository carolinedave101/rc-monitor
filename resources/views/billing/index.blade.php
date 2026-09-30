@extends('layouts.app')

@section('title', 'Billing')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Billing</h1>
        <p class="text-muted small mb-0">Your plan and usage. Invoices are issued here by your provider.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100 p-4">
            <h2 class="h6 text-muted text-uppercase mb-3">Your plan</h2>
            @if ($user->plan)
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div class="h4 fw-bold mb-0">{{ $user->plan->name }}</div>
                        <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $user->plan->billing_type)) }}</div>
                    </div>
                    <div class="text-end">
                        <div class="h4 fw-bold mb-0">{{ $user->plan->priceLabel() }}</div>
                    </div>
                </div>
                @if ($user->plan_activated_at)
                    <div class="small text-muted mb-3">Active since {{ $user->plan_activated_at->format('M j, Y') }}</div>
                @endif
            @else
                <div class="h4 fw-bold mb-1">Pilot</div>
                <p class="text-muted small mb-3">
                    You're on the default pilot limits. Choose a plan below to enroll more devices.
                </p>
            @endif

            <label class="form-label small mb-1">Device usage</label>
            <div class="d-flex justify-content-between small text-muted mb-1">
                <span>{{ $deviceCount }} of {{ $user->deviceLimit() }} devices</span>
                <span>{{ $user->deviceLimit() > 0 ? (int) round($deviceCount / $user->deviceLimit() * 100) : 0 }}%</span>
            </div>
            <div class="progress" style="height: .5rem;">
                <div class="progress-bar" style="width: {{ $user->deviceLimit() > 0 ? min(100, (int) round($deviceCount / $user->deviceLimit() * 100)) : 0 }}%"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header bg-white"><i class="bi bi-stars me-1"></i> Available plans</div>
            <div class="list-group list-group-flush">
                @foreach ($plans as $plan)
                    <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <div class="fw-semibold">
                                {{ $plan->name }}
                                @if ($user->plan_id === $plan->id)
                                    <span class="badge bg-success status-badge ms-1">Current</span>
                                @endif
                            </div>
                            <div class="small text-muted">{{ $plan->description }}</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold">{{ $plan->priceLabel() }}</div>
                            <div class="small text-muted">
                                {{ $plan->device_limit ? $plan->device_limit.' devices' : 'Unlimited devices' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="card-body border-top small text-muted">
                To change plans, ask support — your provider will generate an invoice. Once it's approved,
                your new device limit applies immediately.
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-white"><i class="bi bi-receipt me-1"></i> Invoices</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>For</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th>Due</th>
                    <th class="text-end"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="fw-semibold">{{ $invoice->number }}</td>
                        <td class="small text-muted">
                            {{ $invoice->plan?->name ?? $invoice->serviceStep?->title ?? '—' }}
                        </td>
                        <td class="text-end">{{ $invoice->totalLabel() }}</td>
                        <td><span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span></td>
                        <td class="small text-muted">{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('billing.show', $invoice) }}" class="btn btn-sm btn-outline-primary">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No invoices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
