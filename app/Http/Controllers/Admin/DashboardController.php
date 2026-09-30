<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Device;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'users' => User::count(),
            'devices' => Device::count(),
            'online_devices' => Device::where('last_seen_at', '>=', now()->subMinutes(5))->count(),
            'alerts' => Alert::count(),
            'unread_alerts' => Alert::whereNull('read_at')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
