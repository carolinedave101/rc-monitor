<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Models\AuditLog;
use App\Models\Consent;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $devices = Device::visibleTo($user)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();

        $ownDevices = $devices->where('user_id', $user->id);
        $sharedDevices = $devices->where('user_id', '!=', $user->id);

        return view('devices.index', compact('ownDevices', 'sharedDevices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->devices()->count() >= $user->deviceLimit()) {
            return back()
                ->withErrors(['name' => 'Your plan allows up to '.$user->deviceLimit().' devices. Choose a higher plan to enroll more.'])
                ->withInput();
        }

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

        $this->recordConsent($request, $device, 'enrollment');

        return redirect()->route('devices.show', $device)
            ->with('status', 'Device enrolled. Install the agent with the token below and it will activate on first check-in.');
    }

    public function show(Request $request, Device $device)
    {
        $this->authorizeView($device);

        $isOwner = $device->user_id === Auth::id();

        $validTabs = [
            'overview', 'calls', 'messages', 'locations', 'alerts',
            'apps', 'contacts', 'diagnostics', 'browser', 'emails',
            'media', 'notes', 'calendar',
        ];

        $tab = $request->query('tab', 'overview');

        if (! in_array($tab, $validTabs, true)) {
            $tab = 'overview';
        }

        $calls = $device->calls()->latest('started_at')->limit(100)->get();
        $messages = $device->messages()->latest('sent_at')->limit(200)->get();
        $locations = $device->locations()->latest('recorded_at')->limit(100)->get();
        $messagesPlatforms = $messages->groupBy('platform');
        $alerts = $device->alerts()->latest()->limit(20)->get();

        $appActivities = $device->appActivities()->latest('launched_at')->limit(100)->get();
        $appUsage = $appActivities
            ->groupBy('app_name')
            ->map(fn ($items) => (int) $items->sum('duration_seconds'))
            ->sortDesc()
            ->take(8);
        $contacts = $device->contacts()->orderBy('name')->limit(200)->get();
        $diagnostics = $device->diagnostics()->latest('recorded_at')->limit(24)->get();
        $browser = $device->browserHistories()->latest('visited_at')->limit(100)->get();
        $emails = $device->emails()->latest('sent_at')->limit(100)->get();
        $media = $device->media()->latest('taken_at')->limit(100)->get();
        $notes = $device->notes()->latest('updated_at')->limit(100)->get();
        $calendar = $device->calendarEvents()->latest('starts_at')->limit(100)->get();

        $geofences = AlertRule::query()
            ->where('type', 'geofence')
            ->where('enabled', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where(function ($query) use ($device) {
                $query->where('device_id', $device->id)->orWhereNull('device_id');
            })
            ->get(['id', 'latitude', 'longitude', 'radius_meters', 'severity']);

        $shares = $isOwner
            ? $device->shares()->whereIn('status', ['pending', 'accepted'])->with('viewer')->latest()->get()
            : collect();

        $commands = $isOwner
            ? $device->commands()->latest()->limit(10)->get()
            : collect();

        return view('devices.show', compact(
            'device',
            'tab',
            'isOwner',
            'shares',
            'commands',
            'calls',
            'messages',
            'locations',
            'messagesPlatforms',
            'alerts',
            'appActivities',
            'appUsage',
            'contacts',
            'diagnostics',
            'browser',
            'emails',
            'media',
            'notes',
            'calendar',
            'geofences',
        ));
    }

    public function destroy(Device $device)
    {
        $this->authorizeOwner($device);
        $name = $device->name;
        $device->delete();

        return redirect()->route('devices.index')->with('status', "Device \"{$name}\" removed.");
    }

    public function updateStatus(Request $request, Device $device)
    {
        $this->authorizeOwner($device);

        $status = $request->validate(['status' => 'required|in:pending,active,suspended'])['status'];

        $device->update(['status' => $status]);

        $labels = [
            'active' => 'Device activated',
            'suspended' => 'Device suspended — the agent will stop reporting',
            'pending' => 'Device set to pending',
        ];

        return redirect()->route('devices.show', $device)->with('status', $labels[$status]);
    }

    public function markConsented(Request $request, Device $device)
    {
        $this->authorizeOwner($device);
        $device->update([
            'consent_recorded' => true,
            'consented_at' => now(),
            'status' => 'active',
        ]);

        $this->recordConsent($request, $device, 'enrollment');

        return redirect()->route('devices.show', $device)->with('status', 'Consent recorded. Device is now active.');
    }

    public function rotateToken(Device $device)
    {
        $this->authorizeOwner($device);

        $device->update(['agent_token' => Device::generateToken()]);

        AuditLog::record('device.token.rotated', $device, ['by' => 'owner']);

        return back()->with('status', 'Enrollment token regenerated. Update the agent with the new token — the old one stops working immediately.');
    }

    private function recordConsent(Request $request, Device $device, string $type): void
    {
        Consent::create([
            'user_id' => Auth::id(),
            'device_id' => $device->id,
            'type' => $type,
            'method' => 'in_app',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
            'consented_at' => now(),
        ]);
    }

    private function authorizeOwner(Device $device): void
    {
        abort_unless($device->user_id === Auth::id(), 403);
    }

    private function authorizeView(Device $device): void
    {
        $user = Auth::user();

        abort_unless(
            $device->user_id === $user->id || $device->isSharedWith($user),
            403,
        );
    }
}
