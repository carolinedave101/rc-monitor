<?php

namespace Database\Factories;

use App\Models\DeviceLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceLocation>
 */
class DeviceLocationFactory extends Factory
{
    protected $model = DeviceLocation::class;

    public function definition(): array
    {
        $anchor = $this->faker->randomElement([
            ['Home', 51.5074, -0.1278],
            ['Work', 51.5155, -0.0922],
            ['School', 51.5121, -0.1042],
        ]);

        return [
            'latitude' => round($anchor[1] + $this->faker->randomFloat(4, -0.008, 0.008), 7),
            'longitude' => round($anchor[2] + $this->faker->randomFloat(4, -0.008, 0.008), 7),
            'accuracy_meters' => $this->faker->randomFloat(2, 3, 50),
            'label' => $anchor[0],
            'recorded_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
