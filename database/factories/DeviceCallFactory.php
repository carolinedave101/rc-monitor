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
        return [
            'direction' => $this->faker->randomElement(['incoming', 'outgoing', 'missed']),
            'contact_name' => $this->faker->name(),
            'phone_number' => '+1555'.$this->faker->numerify('#######'),
            'duration_seconds' => $this->faker->numberBetween(0, 3600),
            'started_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
