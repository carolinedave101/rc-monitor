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
        return [
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'accuracy_meters' => $this->faker->randomFloat(2, 3, 50),
            'label' => $this->faker->randomElement(['Home', 'School', 'Work', null]),
            'recorded_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
