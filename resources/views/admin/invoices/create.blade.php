@extends('layouts.admin')

@section('title', 'New invoice')
@section('heading', 'New invoice')
@section('subheading', 'Create a draft, then send it when ready. The customer pays and uploads proof of payment.')

@section('actions')
    <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> All invoices</a>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.invoices.store') }}">
    @csrf
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card p-4">
                <h2 class="h6 text-muted text-uppercase mb-3">Invoice details</h2>

                <div class="mb-3">
                    <label class="form-label">Customer</label>
                    <select name="user_id" class="form-select" required>
                        <option value="">Select a customer…</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id') == $user->id)>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Plan (optional)</label>
                        <select name="plan_id" class="form-select">
                            <option value="">No plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                    {{ $plan->name }} — {{ $plan->priceLabel() }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1">On approval, the customer's plan is updated automatically.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service step (optional)</label>
                        <select name="service_step_id" class="form-select">
                            <option value="">No step</option>
                            @foreach ($steps as $step)
                                <option value="{{ $step->id }}" @selected(old('service_step_id') == $step->id)>
                                    {{ $step->user?->name }} — {{ $step->position }}. {{ $step->title }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small text-muted mt-1">Paying this invoice unblocks the customer's journey step.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="{{ old('description') }}" required maxlength="255" placeholder="e.g. Standard plan — 5 devices">
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Amount (USD)</label>
                        <input type="number" name="amount" class="form-control" value="{{ old('amount', '49.00') }}" step="0.01" min="0.5" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Due date (optional)</label>
                        <input type="date" name="due_at" class="form-control" value="{{ old('due_at') }}">
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label">Internal note (optional)</label>
                    <textarea name="notes" class="form-control" rows="2" maxlength="2000">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card p-4">
                <h2 class="h6 text-muted text-uppercase mb-3">Payment methods</h2>
                <p class="small text-muted">Select every method the customer can use for this invoice.</p>
                @forelse ($methods as $method)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="methods[]" value="{{ $method->id }}" id="method-{{ $method->id }}" @checked(in_array($method->id, old('methods', [])))>
                        <label class="form-check-label" for="method-{{ $method->id }}">
                            {{ $method->label }}
                            <span class="badge text-bg-light border status-badge">{{ $method->typeLabel() }}</span>
                        </label>
                    </div>
                @empty
                    <div class="alert alert-warning mb-0">No payment methods exist yet. Add them from the navigation.</div>
                @endforelse
            </div>

            <div class="d-grid mt-3">
                <button class="btn btn-primary btn-lg">Create draft invoice</button>
            </div>
        </div>
    </div>
</form>
@endsection
