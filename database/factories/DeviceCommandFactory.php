<?php

namespace Database\Factories;

use App\Models\DeviceCommand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceCommand>
 */
class DeviceCommandFactory extends Factory
{
    protected $model = DeviceCommand::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(DeviceCommand::TYPES),
            'status' => 'pending',
            'issued_at' => now(),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => ['status' => 'sent', 'sent_at' => now()]);
    }

    public function acknowledged(): static
    {
        return $this->state(fn () => [
            'status' => 'acknowledged',
            'sent_at' => now()->subMinute(),
            'acknowledged_at' => now(),
            'result' => 'Done',
        ]);
    }
}
