<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Device;
use App\Models\SimulationProfile;
use App\Services\Settings;
use App\Services\SimulationEngine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SimulationController extends Controller
{
    public function __construct(private readonly SimulationEngine $engine) {}

    public function index(): View
    {
        $devices = Device::query()
            ->with(['user', 'simulationProfile'])
            ->latest()
            ->get();

        $enabled = Settings::bool('simulation_enabled', true);

        return view('admin.simulation.index', compact('devices', 'enabled'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $enabled = $request->boolean('simulation_enabled');

        Settings::set('simulation_enabled', $enabled ? '1' : '0');

        AuditLog::record('simulation.settings.updated', null, ['enabled' => $enabled]);

        return back()->with('status', 'Simulation '.($enabled ? 'enabled' : 'paused').' globally.');
    }

    public function updateProfile(Request $request, Device $device): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => 'sometimes|boolean',
            'activity_level' => ['required', Rule::in(SimulationProfile::LEVELS)],
        ]);

        $profile = $device->simulationProfile()->updateOrCreate([], [
            'enabled' => $request->boolean('enabled'),
            'activity_level' => $data['activity_level'],
        ]);

        AuditLog::record('simulation.profile.updated', $profile, [
            'device_id' => $device->id,
            'enabled' => $profile->enabled,
            'activity_level' => $profile->activity_level,
        ]);

        return back()->with('status', "Simulation for {$device->name} updated.");
    }

    public function tick(Device $device): RedirectResponse
    {
        $counts = $this->engine->tickDevice($device);

        AuditLog::record('simulation.tick', $device, $counts);

        return back()->with('status', 'Generated '.array_sum($counts)." simulated records for {$device->name}.");
    }

    public function backfill(Request $request, Device $device): RedirectResponse
    {
        $days = (int) $request->validate([
            'days' => 'required|integer|min:1|max:90',
        ])['days'];

        $counts = $this->engine->backfill($device, $days);

        AuditLog::record('simulation.backfill', $device, ['days' => $days] + $counts);

        return back()->with('status', 'Backfilled '.array_sum($counts)." records over {$days} days for {$device->name}.");
    }

    public function wipe(Device $device): RedirectResponse
    {
        $deleted = $this->engine->wipe($device);

        AuditLog::record('simulation.wipe', $device, $deleted);

        return back()->with('status', 'Removed '.array_sum($deleted)." simulated records for {$device->name}.");
    }
}
