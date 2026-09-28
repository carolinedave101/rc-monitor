<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\DeviceLocation;
use App\Models\DeviceMessage;
use App\Services\AlertEngine;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(private readonly AlertEngine $alertEngine)
    {
    }

    public function heartbeat(Request $request)
    {
        $request->validate([
            'os_version' => 'nullable|string|max:50',
            'manufacturer' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'phone_number' => 'nullable|string|max:30',
        ]);

        $device = $request->device;
        $device->forceFill($request->only('os_version', 'manufacturer', 'model', 'phone_number'))
            ->save();

        return response()->json([
            'status' => 'ok',
            'device' => $device->name,
            'next_checkin' => now()->addMinutes(5)->toDateTimeString(),
        ]);
    }

    public function ingest(Request $request)
    {
        $request->validate([
            'calls' => 'sometimes|array',
            'calls.*.direction' => 'required|in:incoming,outgoing,missed',
            'calls.*.contact_name' => 'nullable|string|max:255',
            'calls.*.phone_number' => 'nullable|string|max:30',
            'calls.*.duration_seconds' => 'nullable|integer|min:0',
            'calls.*.started_at' => 'required|date',
            'messages' => 'sometimes|array',
            'messages.*.platform' => 'nullable|string|max:30',
            'messages.*.direction' => 'required|in:incoming,outgoing',
            'messages.*.contact_name' => 'nullable|string|max:255',
            'messages.*.phone_number' => 'nullable|string|max:30',
            'messages.*.body' => 'required|string',
            'messages.*.was_deleted' => 'nullable|boolean',
            'messages.*.sent_at' => 'required|date',
            'locations' => 'sometimes|array',
            'locations.*.latitude' => 'required|numeric|between:-90,90',
            'locations.*.longitude' => 'required|numeric|between:-180,180',
            'locations.*.accuracy_meters' => 'nullable|numeric|min:0',
            'locations.*.label' => 'nullable|string|max:255',
            'locations.*.recorded_at' => 'required|date',
        ]);

        $device = $request->device;
        $counts = ['calls' => 0, 'messages' => 0, 'locations' => 0];

        foreach ($request->input('calls', []) as $call) {
            DeviceCall::create(array_merge($call, ['device_id' => $device->id]));
            $counts['calls']++;
        }

        foreach ($request->input('messages', []) as $msg) {
            $message = DeviceMessage::create(array_merge($msg, ['device_id' => $device->id]));
            $this->alertEngine->evaluateMessage($device, $message);
            $counts['messages']++;
        }

        foreach ($request->input('locations', []) as $loc) {
            $location = DeviceLocation::create(array_merge($loc, ['device_id' => $device->id]));
            $this->alertEngine->evaluateLocation($device, $location);
            $counts['locations']++;
        }

        return response()->json([
            'status' => 'ok',
            'ingested' => $counts,
        ]);
    }
}