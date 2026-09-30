<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Device;
use App\Models\DeviceCommand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DeviceCommandController extends Controller
{
    public function store(Request $request, Device $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        $type = $request->validate([
            'type' => ['required', Rule::in(DeviceCommand::TYPES)],
        ])['type'];

        $command = $device->commands()->create([
            'requested_by' => $request->user()->id,
            'type' => $type,
            'status' => 'pending',
            'issued_at' => now(),
        ]);

        AuditLog::record('device.command.queued', $command, [
            'device_id' => $device->id,
            'type' => $type,
        ]);

        return back()->with('status', $command->label().' queued. The agent will pick it up at its next check-in.');
    }
}
