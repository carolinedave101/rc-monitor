<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    public function index()
    {
        $alerts = Alert::where('user_id', Auth::id())
            ->with('device')
            ->latest()
            ->paginate(20);

        foreach ($alerts as $alert) {
            if ($alert->read_at === null) {
                $alert->update(['read_at' => now()]);
            }
        }

        return view('alerts.index', compact('alerts'));
    }

    public function rules()
    {
        $rules = AlertRule::where('user_id', Auth::id())->with('device')->get();
        $devices = Device::where('user_id', Auth::id())->get();

        return view('alerts.rules', compact('rules', 'devices'));
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:keyword,geofence',
            'device_id' => 'nullable|exists:devices,id',
            'keyword' => 'required_if:type,keyword|nullable|string|max:255',
            'latitude' => 'required_if:type,geofence|nullable|numeric|between:-90,90',
            'longitude' => 'required_if:type,geofence|nullable|numeric|between:-180,180',
            'radius_meters' => 'required_if:type,geofence|nullable|integer|min:50',
            'severity' => 'required|in:info,warning,critical',
        ]);

        if (isset($data['device_id']) && $data['device_id']) {
            abort_unless(Device::where('id', $data['device_id'])->where('user_id', Auth::id())->exists(), 403);
        }

        Auth::user()->alertRules()->create($data);

        return redirect()->route('alerts.rules')->with('status', 'Alert rule created.');
    }

    public function destroyRule(AlertRule $rule)
    {
        abort_unless($rule->user_id === Auth::id(), 403);
        $rule->delete();

        return redirect()->route('alerts.rules')->with('status', 'Alert rule removed.');
    }

    public function toggleRule(AlertRule $rule)
    {
        abort_unless($rule->user_id === Auth::id(), 403);
        $rule->update(['enabled' => ! $rule->enabled]);

        return redirect()->route('alerts.rules')->with('status', 'Alert rule ' . ($rule->enabled ? 'enabled' : 'disabled') . '.');
    }
}