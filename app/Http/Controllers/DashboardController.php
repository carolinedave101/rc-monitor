<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\DeviceMessage;
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

        $steps = $user->serviceSteps()->get();
        $pausedStep = $steps->first(fn ($step) => $step->isPaused());
        $completedSteps = $steps->where('status', 'completed')->count();
        $progress = $steps->count() > 0 ? (int) round($completedSteps / $steps->count() * 100) : 0;

        [$activityLabels, $activityCalls, $activityMessages] = $this->activitySeries($devices);

        return view('dashboard', compact(
            'devices',
            'unreadAlerts',
            'recentAlerts',
            'activeDevices',
            'onlineDevices',
            'features',
            'steps',
            'pausedStep',
            'completedSteps',
            'progress',
            'activityLabels',
            'activityCalls',
            'activityMessages',
        ));
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, int>, 2: array<int, int>}
     */
    private function activitySeries($devices): array
    {
        $deviceIds = $devices->pluck('id');
        $since = now()->subDays(6)->startOfDay();

        $calls = DeviceCall::query()
            ->whereIn('device_id', $deviceIds)
            ->where('started_at', '>=', $since)
            ->selectRaw('date(started_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $messages = DeviceMessage::query()
            ->whereIn('device_id', $deviceIds)
            ->where('sent_at', '>=', $since)
            ->selectRaw('date(sent_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $callSeries = [];
        $messageSeries = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->toDateString();

            $labels[] = $date->format('D');
            $callSeries[] = (int) ($calls[$key] ?? 0);
            $messageSeries[] = (int) ($messages[$key] ?? 0);
        }

        return [$labels, $callSeries, $messageSeries];
    }
}
