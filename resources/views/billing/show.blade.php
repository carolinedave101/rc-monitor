@extends('layouts.app')

@section('title', 'Invoice '.$invoice->number)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Invoice {{ $invoice->number }}</h1>
        <p class="text-muted small mb-0">
            Issued {{ $invoice->issued_at?->format('M j, Y') ?? '—' }}
            @if ($invoice->due_at)
                · due {{ $invoice->due_at->format('M j, Y') }}
            @endif
        </p>
    </div>
    <span class="badge text-bg-{{ $invoice->statusColor() }} fs-6">{{ $invoice->statusLabel() }}</span>
</div>

@if ($invoice->rejection_reason)
    <div class="alert alert-danger border-0 rounded-4">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Payment not verified:</strong> {{ $invoice->rejection_reason }}
        <div class="small mt-1">You can upload a new proof of payment below.</div>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white">Details</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($item->totalCents() / 100, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="2" class="text-end">Total due</th>
                                <th class="text-end">{{ $invoice->totalLabel() }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($invoice->notes)
                    <div class="alert alert-light border small mb-0"><strong>Note from your provider:</strong> {{ $invoice->notes }}</div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-white">Payment history</div>
            <div class="list-group list-group-flush">
                @forelse ($invoice->payments as $payment)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div>
                                <div class="fw-semibold">
                                    {{ $payment->amountLabel() }}
                                    <span class="badge text-bg-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'rejected' ? 'danger' : 'info') }} status-badge ms-1">
                                        {{ str_replace('_', ' ', ucfirst($payment->status)) }}
                                    </span>
                                </div>
                                <div class="small text-muted">
                                    {{ $payment->paymentMethod?->label ?? '—' }}
                                    @if ($payment->reference) · ref {{ $payment->reference }} @endif
                                    · {{ $payment->created_at->format('M j, Y g:i A') }}
                                </div>
                                @if ($payment->rejection_reason)
                                    <div class="small text-danger mt-1">{{ $payment->rejection_reason }}</div>
                                @endif
                            </div>
                            @if ($payment->status === 'pending_verification')
                                <span class="badge bg-info status-badge">Under review</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">No payments submitted yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white"><i class="bi bi-credit-card me-1"></i> How to pay</div>
            <div class="card-body">
                @if ($invoice->status === 'awaiting_verification')
                    <div class="alert alert-info border-0 rounded-4 mb-0">
                        <i class="bi bi-hourglass-split me-1"></i>
                        We received your proof of payment and are verifying it. You'll be notified as soon as it's approved.
                    </div>
                @elseif ($invoice->isPaid())
                    <div class="alert alert-success border-0 rounded-4 mb-0">
                        <i class="bi bi-check2-circle me-1"></i>
                        This invoice is paid. Thank you!
                    </div>
                @elseif ($invoice->status === 'void')
                    <div class="alert alert-secondary border-0 rounded-4 mb-0">This invoice was voided.</div>
                @else
                    <p class="small text-muted">Choose one of the methods below, pay {{ $invoice->totalLabel() }}, then upload your proof of payment.</p>

                    <div class="accordion mb-3" id="methods">
                        @foreach ($invoice->paymentMethods as $index => $method)
                            <div class="accordion-item border rounded-3 mb-2">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }} rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#method-{{ $method->id }}">
                                        {{ $method->label }}
                                    </button>
                                </h2>
                                <div id="method-{{ $method->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#methods">
                                    <div class="accordion-body small">
                                        <pre class="mb-0 text-wrap font-monospace" style="white-space: pre-wrap;">{{ $method->details ?? 'Contact support for payment details.' }}</pre>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <form method="POST" action="{{ route('billing.payments.store', $invoice) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small">Payment method used</label>
                            <select name="payment_method_id" class="form-select" required>
                                @foreach ($invoice->paymentMethods as $method)
                                    <option value="{{ $method->id }}">{{ $method->label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Reference / transaction ID (optional)</label>
                            <input type="text" name="reference" class="form-control" maxlength="255" placeholder="e.g. transfer reference">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small">Proof of payment</label>
                            <input type="file" name="proof" class="form-control" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                            <div class="small text-muted mt-1">Screenshot or receipt — JPG, PNG, WEBP or PDF, up to 5 MB.</div>
                        </div>
                        <button class="btn btn-primary w-100">Upload proof of payment</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
