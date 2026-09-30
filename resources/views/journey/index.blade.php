@extends('layouts.app')

@section('title', 'My Plan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="page-head h3 mb-1">Your service plan</h1>
        <p class="text-muted small mb-0">Every step of your rollout, in one place.</p>
    </div>
</div>

@php $pausedStep = $steps->first(fn ($step) => $step->isPaused()); @endphp

@if ($pausedStep)
    <div class="alert alert-warning d-flex gap-2 align-items-start shadow-sm rounded-4">
        <i class="bi bi-pause-circle-fill fs-5"></i>
        <div>
            <strong>Your plan is paused.</strong>
            @if ($pausedStep->paused_reason)
                <div class="small mt-1">{{ $pausedStep->paused_reason }}</div>
            @endif
            <div class="small mt-1 text-muted">We'll continue as soon as the pause is lifted. Contact support if you have questions.</div>
        </div>
    </div>
@endif

<div class="row g-4">
    @forelse ($steps as $step)
        <div class="col-lg-6">
            <div class="card h-100 p-4">
                <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-light fw-bold">{{ $step->position }}</span>
                        <div>
                            <div class="fw-semibold">{{ $step->title }}</div>
                            @if ($step->description)
                                <div class="small text-muted">{{ $step->description }}</div>
                            @endif
                        </div>
                    </div>
                    <span class="badge text-bg-{{ $step->isPaused() ? 'dark' : $step->statusColor() }} status-badge">
                        {{ $step->isPaused() ? 'Paused' : $step->statusLabel() }}
                    </span>
                </div>

                @if ($step->isPaused() && $step->paused_reason)
                    <div class="small text-danger"><i class="bi bi-info-circle me-1"></i>{{ $step->paused_reason }}</div>
                @endif

                @if ($step->requires_payment && ! $step->isCompleted())
                    <div class="small text-muted"><i class="bi bi-credit-card me-1"></i>Payment required before this step can complete.</div>
                @endif

                @if ($step->completed_at)
                    <div class="small text-success mt-2"><i class="bi bi-check-circle-fill me-1"></i>Completed {{ $step->completed_at->format('M j, Y') }}</div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-signpost-split" style="font-size: 3rem;"></i>
                    <p class="mt-2 mb-0">Your plan is being prepared. It will appear here shortly.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>
@endsection
