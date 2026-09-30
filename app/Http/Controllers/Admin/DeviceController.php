<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Device;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(): View
    {
        $devices = Device::query()
            ->with('user')
            ->latest()
            ->paginate(25);

        return view('admin.devices.index', compact('devices'));
    }

    public function updateStatus(Request $request, Device $device): RedirectResponse
    {
        $status = $request->validate([
            'status' => ['required', Rule::in(['pending', 'active', 'suspended'])],
        ])['status'];

        $previous = $device->status;

        $device->update(['status' => $status]);

        AuditLog::record('device.status.updated', $device, [
            'from' => $previous,
            'to' => $status,
            'owner_id' => $device->user_id,
        ]);

        return back()->with('status', "\"{$device->name}\" is now {$status}.");
    }

    public function rotateToken(Device $device): RedirectResponse
    {
        $device->update(['agent_token' => Device::generateToken()]);

        AuditLog::record('device.token.rotated', $device, [
            'owner_id' => $device->user_id,
        ]);

        return back()->with('status', "Agent token for \"{$device->name}\" was rotated. The agent must use the new token.");
    }
}
