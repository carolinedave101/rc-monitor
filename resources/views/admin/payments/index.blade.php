@extends('layouts.admin')

@section('title', 'Payments')
@section('heading', 'Payment verification')
@section('subheading', 'Review uploaded proofs of payment, then approve or reject with a reason.')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Awaiting verification</span>
        <span class="badge text-bg-light border">{{ $pending->count() }}</span>
    </div>
    <div class="list-group list-group-flush">
        @forelse ($pending as $payment)
            <div class="list-group-item py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-4">
                        <div class="fw-semibold">{{ $payment->user?->name ?? '—' }} · {{ $payment->amountLabel() }}</div>
                        <div class="small text-muted">
                            Invoice
                            @if ($payment->invoice)
                                <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="text-decoration-none">{{ $payment->invoice->number }}</a>
                            @endif
                            · {{ $payment->paymentMethod?->label ?? 'Manual' }}
                            · {{ $payment->created_at->diffForHumans() }}
                        </div>
                        @if ($payment->reference)
                            <div class="small text-muted">Ref: {{ $payment->reference }}</div>
                        @endif
                    </div>
                    <div class="col-md-3">
                        @if ($payment->proof_path)
                            <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-file-earmark-image me-1"></i> View proof
                            </a>
                        @else
                            <span class="small text-muted">No proof file</span>
                        @endif
                    </div>
                    <div class="col-md-5">
                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                            <form method="POST" action="{{ route('admin.payments.approve', $payment) }}">
                                @csrf
                                <button class="btn btn-sm btn-success px-3">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="d-flex gap-2">
                                @csrf
                                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Reason for rejection" required maxlength="1000" style="min-width: 14rem;">
                                <button class="btn btn-sm btn-outline-danger px-3">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="list-group-item text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                <p class="mt-2 mb-0">No payments waiting for verification.</p>
            </div>
        @endforelse
    </div>
</div>

<div class="card">
    <div class="card-header">Recently reviewed</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Invoice</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th>Reviewed by</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviewed as $payment)
                    <tr>
                        <td>{{ $payment->user?->name ?? '—' }}</td>
                        <td>
                            @if ($payment->invoice)
                                <a href="{{ route('admin.invoices.show', $payment->invoice) }}" class="text-decoration-none">{{ $payment->invoice->number }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end">{{ $payment->amountLabel() }}</td>
                        <td>
                            <span class="badge text-bg-{{ $payment->status === 'approved' ? 'success' : 'danger' }}">{{ ucfirst($payment->status) }}</span>
                        </td>
                        <td class="small text-muted">{{ $payment->reviewedBy?->name ?? '—' }}</td>
                        <td class="small text-muted">{{ $payment->reviewed_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Nothing reviewed yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
