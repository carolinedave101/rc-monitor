<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = Auth::user();

        $devices = Device::where('user_id', $user->id)->get();
        $unreadAlerts = Alert::where('user_id', $user->id)->unread()->count();
        $recentAlerts = Alert::where('user_id', $user->id)
            ->with('device')
            ->latest()
            ->limit(8)
            ->get();

        $activeDevices = $devices->where('status', 'active')->count();
        $onlineDevices = $devices->filter(fn ($d) => $d->isOnline())->count();

        $features = Feature::query()->public()->ordered()->get();

        return view('dashboard', compact('devices', 'unreadAlerts', 'recentAlerts', 'activeDevices', 'onlineDevices', 'features'));
    }
}
