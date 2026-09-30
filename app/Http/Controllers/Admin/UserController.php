<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->withCount(['devices', 'alerts'])
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['devices' => fn ($query) => $query->latest()])
            ->load(['alerts' => fn ($query) => $query->latest()->limit(10)])
            ->load(['serviceSteps', 'plan']);

        $plans = Plan::query()->active()->ordered()->get();

        return view('admin.users.show', compact('user', 'plans'));
    }

    public function updatePlan(Request $request, User $user): RedirectResponse
    {
        $planId = $request->validate([
            'plan_id' => ['nullable', Rule::exists('plans', 'id')],
        ])['plan_id'] ?? null;

        $plan = $planId ? Plan::find($planId) : null;

        $user->update([
            'plan_id' => $plan?->id,
            'plan_activated_at' => $plan ? now() : null,
        ]);

        AuditLog::record('user.plan.updated', $user, [
            'plan' => $plan?->code,
        ]);

        return back()->with('status', 'Plan updated to '.($plan?->name ?? 'no plan').'.');
    }
}
