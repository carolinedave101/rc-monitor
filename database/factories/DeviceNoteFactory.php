<?php

namespace Database\Factories;

use App\Models\DeviceNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceNote>
 */
class DeviceNoteFactory extends Factory
{
    protected $model = DeviceNote::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->randomElement(['Shopping list', 'Homework', 'Ideas', 'Reminder', 'Trip plan']),
            'body' => $this->faker->sentence(10),
            'source' => 'simulated',
        ];
    }
}
