<?php

namespace App\Console\Commands;

use App\Models\Device;
use App\Services\Settings;
use App\Services\SimulationEngine;
use Illuminate\Console\Command;

class SimulateDevices extends Command
{
    protected $signature = 'simulate:devices {--device= : Only simulate the given device id}';

    protected $description = 'Generate simulated activity for devices with an enabled simulation profile';

    public function handle(SimulationEngine $engine): int
    {
        if (! Settings::bool('simulation_enabled', true)) {
            $this->info('Simulation is disabled globally.');

            return self::SUCCESS;
        }

        $devices = Device::query()
            ->when($this->option('device'), fn ($query, $id) => $query->whereKey($id))
            ->whereHas('simulationProfile', fn ($query) => $query->where('enabled', true))
            ->get();

        if ($devices->isEmpty()) {
            $this->info('No devices have simulation enabled.');

            return self::SUCCESS;
        }

        foreach ($devices as $device) {
            $counts = $engine->tickDevice($device);
            $summary = collect($counts)->map(fn ($count, $domain) => "{$domain}={$count}")->implode(' ');

            $this->line("{$device->name}: {$summary}");
        }

        return self::SUCCESS;
    }
}
