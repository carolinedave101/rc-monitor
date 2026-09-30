@extends('layouts.admin')

@section('title', 'Features')
@section('heading', 'Feature registry')
@section('subheading', 'Set the status of every advertised capability. Changes are audit-logged and visible to customers when public.')

@section('content')
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Feature</th>
                    <th>Category</th>
                    <th style="min-width: 22rem;">Status &amp; visibility</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($features as $feature)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $feature->name }}</div>
                            <code class="small text-muted">{{ $feature->code }}</code>
                        </td>
                        <td class="text-muted small">{{ ucfirst($feature->category) }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.features.update', $feature) }}" class="d-flex align-items-center gap-3 flex-wrap">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" style="width: 10rem;">
                                    @foreach (\App\Models\Feature::STATUSES as $status)
                                        <option value="{{ $status }}" @selected($feature->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                    @endforeach
                                </select>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="is_public" value="1" id="public-{{ $feature->id }}" @checked($feature->is_public)>
                                    <label class="form-check-label small" for="public-{{ $feature->id }}">Customer visible</label>
                                </div>
                                <button class="btn btn-sm btn-primary px-3">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
