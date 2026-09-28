@extends('layouts.app')

@section('title', 'Enroll device')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="mb-4 text-center">
            <span class="stat-icon icon-primary mb-2"><i class="bi bi-phone-vibrate"></i></span>
            <h1 class="page-head h4 mb-1">Enroll a new device</h1>
            <p class="text-muted small mb-0">Register a device you own or are authorized to monitor. Consent is recorded before monitoring begins.</p>
        </div>
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('devices.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Device name</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manufacturer" class="form-label">Manufacturer</label>
                            <input type="text" class="form-control" id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}" placeholder="e.g. Google">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="model" name="model" value="{{ old('model') }}" placeholder="e.g. Pixel 8">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="os" class="form-label">Operating system</label>
                            <select class="form-select" id="os" name="os">
                                <option value="android" @selected(old('os') === 'android')>Android</option>
                                <option value="ios" @selected(old('os') === 'ios')>iOS</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label">Phone number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" placeholder="+1 555 000 0000">
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-4 p-3 mb-4">
                        <div class="form-check mb-0">
                            <input class="form-check-input @error('consent_recorded') is-invalid @enderror" type="checkbox" value="1" name="consent_recorded" id="consent_recorded" required>
                            <label class="form-check-label" for="consent_recorded">
                                I confirm this device's owner has given informed consent for monitoring.
                            </label>
                            @error('consent_recorded')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-plus me-1"></i> Enroll device</button>
                        <a href="{{ route('devices.index') }}" class="btn btn-link">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection