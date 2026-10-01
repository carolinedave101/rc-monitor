<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AuditLog;
use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\DeviceMessage;
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

        [$activityLabels, $activityCalls, $activityMessages, $activityAlerts] = $this->activitySeries();

        return view('admin.dashboard', compact(
            'stats',
            'recentActivity',
            'activityLabels',
            'activityCalls',
            'activityMessages',
            'activityAlerts',
        ));
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, int>, 2: array<int, int>, 3: array<int, int>}
     */
    private function activitySeries(): array
    {
        $since = now()->subDays(13)->startOfDay();

        $calls = DeviceCall::query()
            ->where('started_at', '>=', $since)
            ->selectRaw('date(started_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $messages = DeviceMessage::query()
            ->where('sent_at', '>=', $since)
            ->selectRaw('date(sent_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $alerts = Alert::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $callSeries = [];
        $messageSeries = [];
        $alertSeries = [];

        for ($i = 13; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();

            $labels[] = $date->format('M j');
            $callSeries[] = (int) ($calls[$key] ?? 0);
            $messageSeries[] = (int) ($messages[$key] ?? 0);
            $alertSeries[] = (int) ($alerts[$key] ?? 0);
        }

        return [$labels, $callSeries, $messageSeries, $alertSeries];
    }
}
