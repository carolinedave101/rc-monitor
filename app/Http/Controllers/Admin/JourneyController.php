<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceStep;
use App\Models\User;
use App\Services\JourneyManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class JourneyController extends Controller
{
    public function __construct(private readonly JourneyManager $journey) {}

    public function store(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'requires_payment' => 'sometimes|boolean',
        ]);

        $this->journey->addStep($user, $data);

        return back()->with('status', 'Step added to the plan.');
    }

    public function start(ServiceStep $step): RedirectResponse
    {
        $this->journey->start($step);

        return back()->with('status', "\"{$step->title}\" started.");
    }

    public function advance(ServiceStep $step): RedirectResponse
    {
        $this->journey->advance($step);

        return back()->with('status', "\"{$step->title}\" completed.");
    }

    public function pause(Request $request, ServiceStep $step): RedirectResponse
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
            'suspend_services' => 'sometimes|boolean',
        ]);

        $this->journey->pause($step, $data['reason'], $request->boolean('suspend_services'));

        return back()->with('status', "\"{$step->title}\" paused. The customer can see the reason on their account.");
    }

    public function resume(ServiceStep $step): RedirectResponse
    {
        $this->journey->resume($step);

        return back()->with('status', "\"{$step->title}\" resumed.");
    }

    public function destroy(ServiceStep $step): RedirectResponse
    {
        $title = $step->title;

        $this->journey->remove($step);

        return back()->with('status', "\"{$title}\" removed from the plan.");
    }
}
