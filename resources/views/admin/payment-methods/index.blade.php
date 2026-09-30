@extends('layouts.admin')

@section('title', 'Payment methods')
@section('heading', 'Payment methods')
@section('subheading', 'Add the ways customers can pay — bank transfer, PayPal, CashApp, crypto or anything else.')

@section('content')
<div class="card mb-4">
    <div class="card-header">Add a method</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payment-methods.store') }}" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-3">
                <label class="form-label small">Label</label>
                <input type="text" name="label" class="form-control form-control-sm" required maxlength="255" placeholder="e.g. PayPal">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Type</label>
                <select name="type" class="form-select form-select-sm">
                    @foreach (\App\Models\PaymentMethod::TYPES as $type)
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small">Details shown to the customer</label>
                <textarea name="details" class="form-control form-control-sm" rows="2" maxlength="2000" placeholder="PayPal email, bank details, wallet address…"></textarea>
            </div>
            <div class="col-md-2 text-end">
                <button class="btn btn-sm btn-primary w-100">Add</button>
            </div>
        </form>
    </div>
</div>

@foreach ($methods as $method)
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.payment-methods.update', $method) }}" class="row g-3 align-items-end">
                @csrf
                @method('PATCH')
                <div class="col-md-3">
                    <label class="form-label small">Label</label>
                    <input type="text" name="label" class="form-control form-control-sm" value="{{ $method->label }}" required maxlength="255">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        @foreach (\App\Models\PaymentMethod::TYPES as $type)
                            <option value="{{ $type }}" @selected($method->type === $type)>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Details shown to the customer</label>
                    <textarea name="details" class="form-control form-control-sm" rows="2" maxlength="2000">{{ $method->details }}</textarea>
                </div>
                <div class="col-md-2">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled-{{ $method->id }}" @checked($method->enabled)>
                        <label class="form-check-label small" for="enabled-{{ $method->id }}">Enabled</label>
                    </div>
                    <button class="btn btn-sm btn-primary w-100">Save</button>
                </div>
            </form>
        </div>
    </div>
@endforeach
@endsection
