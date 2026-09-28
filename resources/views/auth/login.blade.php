@extends('layouts.app')

@section('title', 'Log in')

@section('content')
<div class="auth-shell">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo-transparent.png') }}" alt="ROYALTRICO logo" class="auth-logo">
        <h4 class="page-head mt-3 mb-1">Welcome back</h4>
        <p class="text-muted small mb-0">Log in to your ROYALTRICO dashboard.</p>
    </div>
    <div class="card border-0 rounded-4 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button class="btn btn-primary w-100 py-2" type="submit"><i class="bi bi-box-arrow-in-right me-1"></i> Log in</button>
            </form>
            <p class="mt-3 mb-0 text-center small text-muted">
                No account? <a href="{{ route('register') }}" class="fw-semibold">Register</a>
            </p>
        </div>
    </div>
</div>
@endsection