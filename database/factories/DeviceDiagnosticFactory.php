<?php

namespace Database\Factories;

use App\Models\DeviceDiagnostic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceDiagnostic>
 */
class DeviceDiagnosticFactory extends Factory
{
    protected $model = DeviceDiagnostic::class;

    public function definition(): array
    {
        return [
            'battery_percent' => $this->faker->numberBetween(5, 100),
            'is_charging' => $this->faker->boolean(30),
            'storage_used_mb' => $this->faker->numberBetween(20_000, 110_000),
            'storage_total_mb' => 128_000,
            'network' => $this->faker->randomElement(['wifi', '5g', '4g']),
            'recorded_at' => $this->faker->dateTimeBetween('-1 month'),
            'source' => 'simulated',
        ];
    }
}
