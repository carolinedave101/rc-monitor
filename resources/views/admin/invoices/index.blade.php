@extends('layouts.admin')

@section('title', 'Invoices')
@section('heading', 'Invoices')
@section('subheading', 'Issue invoices, attach payment methods and track payment status.')

@section('actions')
    <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i> New invoice</a>
@endsection

@section('content')
<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm {{ $status ? 'btn-outline-secondary' : 'btn-primary' }}">All</a>
    @foreach (\App\Models\Invoice::STATUSES as $state)
        <a href="{{ route('admin.invoices.index', ['status' => $state]) }}" class="btn btn-sm {{ $status === $state ? 'btn-primary' : 'btn-outline-secondary' }}">
            {{ ucfirst(str_replace('_', ' ', $state)) }}
        </a>
    @endforeach
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Customer</th>
                    <th>For</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th>Issued</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td><span class="fw-semibold">{{ $invoice->number }}</span></td>
                        <td>
                            @if ($invoice->user)
                                <a href="{{ route('admin.users.show', $invoice->user) }}" class="text-decoration-none">{{ $invoice->user->name }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            @if ($invoice->plan)
                                Plan: {{ $invoice->plan->name }}
                            @elseif ($invoice->serviceStep)
                                Step: {{ $invoice->serviceStep->title }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-end fw-semibold">{{ $invoice->totalLabel() }}</td>
                        <td><span class="badge text-bg-{{ $invoice->statusColor() }}">{{ $invoice->statusLabel() }}</span></td>
                        <td class="small text-muted">{{ $invoice->issued_at?->format('M j, Y') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.invoices.show', $invoice) }}" class="btn btn-sm btn-outline-primary">Open</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No invoices yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($invoices->hasPages())
        <div class="card-body">{{ $invoices->links() }}</div>
    @endif
</div>
@endsection
