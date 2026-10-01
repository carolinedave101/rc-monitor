<?php

namespace Database\Factories;

use App\Models\DeviceCall;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceCall>
 */
class DeviceCallFactory extends Factory
{
    protected $model = DeviceCall::class;

    public function definition(): array
    {
        $direction = $this->faker->randomElement(['incoming', 'outgoing', 'outgoing', 'missed']);

        return [
            'direction' => $direction,
            'contact_name' => $this->faker->name(),
            'phone_number' => '+1555'.$this->faker->numerify('#######'),
            'duration_seconds' => $direction === 'missed' ? 0 : $this->faker->numberBetween(20, 1800),
            'started_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
