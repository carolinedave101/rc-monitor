<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Device;
use App\Models\Invoice;
use App\Models\Payment;
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
            'pending_payments' => Payment::where('status', 'pending_verification')->count(),
            'outstanding_invoices' => Invoice::query()->outstanding()->count(),
        ];

        $recentActivity = AuditLog::query()->with('user')->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }
}
