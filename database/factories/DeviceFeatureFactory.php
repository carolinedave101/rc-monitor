<?php

namespace Database\Factories;

use App\Models\DeviceFeature;
use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceFeature>
 */
class DeviceFeatureFactory extends Factory
{
    protected $model = DeviceFeature::class;

    public function definition(): array
    {
        return [
            'feature_id' => Feature::factory(),
            'enabled' => true,
            'last_sync_at' => now(),
        ];
    }
}
