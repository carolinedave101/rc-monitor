@extends('layouts.admin')

@section('title', $invoice->number)
@section('heading', $invoice->number)
@section('subheading', $invoice->user?->name.' · '.$invoice->user?->email)

@section('actions')
    <div class="d-flex gap-2 flex-wrap">
        @if (in_array($invoice->status, ['draft', 'rejected'], true))
            <form method="POST" action="{{ route('admin.invoices.send', $invoice) }}">
                @csrf
                <button class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i> Send to customer</button>
            </form>
        @endif
        @if (! $invoice->isPaid() && $invoice->status !== 'void')
            <form method="POST" action="{{ route('admin.invoices.mark-paid', $invoice) }}" onsubmit="return confirm('Mark this invoice as paid without proof verification?')">
                @csrf
                <button class="btn btn-success btn-sm"><i class="bi bi-check2-circle me-1"></i> Mark paid</button>
            </form>
            <form method="POST" action="{{ route('admin.invoices.void', $invoice) }}" onsubmit="return confirm('Void this invoice?')">
                @csrf
                <button class="btn btn-outline-danger btn-sm">Void</button>
            </form>
        @endif
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All invoices</a>
    </div>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Invoice</span>
                <span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span>
            </div>
            <div class="card-body">
                <div class="row small mb-3">
                    <div class="col-sm-6">
                        <div class="text-muted">Customer</div>
                        <div class="fw-semibold">
                            @if ($invoice->user)
                                <a href="{{ route('admin.users.show', $invoice->user) }}" class="text-decoration-none">{{ $invoice->user->name }}</a>
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="text-muted">Linked to</div>
                        <div class="fw-semibold">
                            @if ($invoice->plan)
                                Plan: {{ $invoice->plan->name }}
                            @elseif ($invoice->serviceStep)
                                Step {{ $invoice->serviceStep->position }}: {{ $invoice->serviceStep->title }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <div class="text-muted">Issued</div>
                        <div>{{ $invoice->issued_at?->format('M j, Y g:i A') ?? 'Not sent yet' }}</div>
                    </div>
                    <div class="col-sm-6 mt-3">
                        <div class="text-muted">Due</div>
                        <div>{{ $invoice->due_at?->format('M j, Y') ?? '—' }}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Unit</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($invoice->items as $item)
                                <tr>
                                    <td>{{ $item->description }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                                    <td class="text-end fw-semibold">${{ number_format($item->totalCents() / 100, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">{{ $invoice->totalLabel() }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if ($invoice->notes)
                    <div class="alert alert-light border small mb-0"><strong>Note:</strong> {{ $invoice->notes }}</div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">Payments ({!! $invoice->payments->count() !!})</div>
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
                                    {{ $payment->paymentMethod?->label ?? 'Manual' }}
                                    @if ($payment->reference) · ref {{ $payment->reference }} @endif
                                    · {{ $payment->created_at->format('M j, Y g:i A') }}
                                </div>
                                @if ($payment->rejection_reason)
                                    <div class="small text-danger mt-1">Rejected: {{ $payment->rejection_reason }}</div>
                                @endif
                            </div>
                            <div class="d-flex gap-2">
                                @if ($payment->proof_path)
                                    <a href="{{ route('admin.payments.proof', $payment) }}" target="_blank" class="btn btn-sm btn-outline-secondary">Proof</a>
                                @endif
                                @if ($payment->status === 'pending_verification')
                                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-primary">Review</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">No payments submitted yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card p-4">
            <h2 class="h6 text-muted text-uppercase mb-3">Payment methods</h2>
            <p class="small text-muted">The customer can choose any checked method on this invoice.</p>
            <form method="POST" action="{{ route('admin.invoices.methods', $invoice) }}">
                @csrf
                @forelse ($methods as $method)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="methods[]" value="{{ $method->id }}" id="inv-method-{{ $method->id }}" @checked($invoice->paymentMethods->contains($method->id))>
                        <label class="form-check-label" for="inv-method-{{ $method->id }}">
                            {{ $method->label }}
                            <span class="badge text-bg-light border status-badge">{{ $method->typeLabel() }}</span>
                        </label>
                    </div>
                @empty
                    <div class="alert alert-warning mb-0">No payment methods exist yet.</div>
                @endforelse
                <button class="btn btn-sm btn-primary mt-3">Save methods</button>
            </form>
        </div>

        @if ($invoice->rejection_reason)
            <div class="card mt-3 p-4 border-start border-4 border-danger">
                <h2 class="h6 text-danger mb-1">Last rejection reason</h2>
                <p class="small mb-0">{{ $invoice->rejection_reason }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
