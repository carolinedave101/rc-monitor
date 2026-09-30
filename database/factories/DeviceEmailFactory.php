<?php

namespace Database\Factories;

use App\Models\DeviceEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceEmail>
 */
class DeviceEmailFactory extends Factory
{
    protected $model = DeviceEmail::class;

    public function definition(): array
    {
        return [
            'direction' => $this->faker->randomElement(['incoming', 'outgoing']),
            'address' => $this->faker->safeEmail(),
            'subject' => $this->faker->sentence(4),
            'snippet' => $this->faker->sentence(12),
            'sent_at' => $this->faker->dateTimeBetween('-1 month'),
            'source' => 'simulated',
        ];
    }
}
