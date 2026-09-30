<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'pilot',
                'name' => 'Pilot',
                'description' => 'Everything in the dashboard for up to 2 devices while you evaluate ROYALTRICO.',
                'price_cents' => 0,
                'device_limit' => 2,
                'billing_type' => 'one_time',
                'sort' => 10,
            ],
            [
                'code' => 'standard',
                'name' => 'Standard',
                'description' => 'One simple plan. Up to five devices, no monthly fees, 14-day money-back guarantee.',
                'price_cents' => 4900,
                'device_limit' => 5,
                'billing_type' => 'one_time',
                'sort' => 20,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
