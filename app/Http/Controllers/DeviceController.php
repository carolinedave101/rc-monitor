<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::where('user_id', Auth::id())->orderByDesc('created_at')->get();

        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'manufacturer' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'os' => 'required|in:android,ios',
            'phone_number' => 'nullable|string|max:30',
            'consent_recorded' => 'required|accepted',
        ]);

        $device = Auth::user()->devices()->create(array_merge($data, [
            'status' => 'pending',
            'consented_at' => now(),
            'agent_token' => Device::generateToken(),
        ]));

        return redirect()->route('devices.show', $device)
            ->with('status', 'Device enrolled. Install the agent with the token below and it will activate on first check-in.');
    }

    public function show(Request $request, Device $device)
    {
        $this->authorizeDevice($device);

        $tab = $request->query('tab', 'overview');

        $calls = $device->calls()->latest('started_at')->limit(100)->get();
        $messages = $device->messages()->latest('sent_at')->limit(200)->get();
        $locations = $device->locations()->latest('recorded_at')->limit(100)->get();
        $messagesPlatforms = $messages->groupBy('platform');
        $alerts = $device->alerts()->latest()->limit(20)->get();

        return view('devices.show', compact('device', 'tab', 'calls', 'messages', 'locations', 'messagesPlatforms', 'alerts'));
    }

    public function destroy(Device $device)
    {
        $this->authorizeDevice($device);
        $name = $device->name;
        $device->delete();

        return redirect()->route('devices.index')->with('status', "Device \"{$name}\" removed.");
    }

    public function updateStatus(Request $request, Device $device)
    {
        $this->authorizeDevice($device);

        $status = $request->validate(['status' => 'required|in:pending,active,suspended'])['status'];

        $device->update(['status' => $status]);

        $labels = [
            'active' => 'Device activated',
            'suspended' => 'Device suspended — the agent will stop reporting',
            'pending' => 'Device set to pending',
        ];

        return redirect()->route('devices.show', $device)->with('status', $labels[$status]);
    }

    public function markConsented(Device $device)
    {
        $this->authorizeDevice($device);

        $device->update([
            'consent_recorded' => true,
            'consented_at' => now(),
            'status' => 'active',
        ]);

        return redirect()->route('devices.show', $device)->with('status', 'Consent recorded. Device is now active.');
    }

    private function authorizeDevice(Device $device): void
    {
        abort_unless($device->user_id === Auth::id(), 403);
    }
}
