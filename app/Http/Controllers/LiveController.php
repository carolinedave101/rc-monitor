<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $devices = $user->devices()->get();
        $unread = Alert::where('user_id', $user->id)->unread()->count();
        $recentAlerts = Alert::where('user_id', $user->id)
            ->with('device')
            ->latest()
            ->limit(8)
            ->get();

        return response()->json([
            'stats' => [
                'total' => $devices->count(),
                'active' => $devices->where('status', 'active')->count(),
                'online' => $devices->filter(fn ($device) => $device->isOnline())->count(),
                'unread' => $unread,
                'notifications' => $user->unreadNotifications()->count(),
            ],
            'devices' => $devices->map(fn (Device $device) => [
                'id' => $device->id,
                'status' => $device->status,
                'online' => $device->isOnline(),
                'last_seen_human' => $device->last_seen_at?->diffForHumans() ?? 'Never',
            ])->values(),
            'alerts' => $recentAlerts->map(fn (Alert $alert) => [
                'id' => $alert->id,
                'title' => $alert->title,
                'severity' => $alert->severity,
                'device' => $alert->device?->name ?? 'All devices',
                'created_at_human' => $alert->created_at->diffForHumans(),
            ])->values(),
        ]);
    }

    public function device(Request $request, Device $device): JsonResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        return response()->json([
            'status' => $device->status,
            'online' => $device->isOnline(),
            'last_seen_human' => $device->last_seen_at?->diffForHumans() ?? 'Never',
        ]);
    }
}
