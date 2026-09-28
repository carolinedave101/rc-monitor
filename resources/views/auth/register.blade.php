@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="auth-shell">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO logo" class="auth-logo">
        <h4 class="page-head mt-3 mb-1">Create your account</h4>
        <p class="text-muted small mb-0">Consent-based monitoring for families, caregivers and employers.</p>
    </div>
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password_confirmation">Confirm password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="agree_terms" name="agree_terms" value="1" class="form-check-input" required>
                    <label class="form-check-label" for="agree_terms">
                        I confirm I will only monitor devices I own or am authorized to monitor.
                    </label>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit"><i class="bi bi-person-plus me-1"></i> Create account</button>
            </form>
            <p class="mt-3 mb-0 text-center small text-muted">
                Already registered? <a href="{{ route('login') }}" class="fw-semibold">Log in</a>
            </p>
        </div>
    </div>
</div>
@endsection