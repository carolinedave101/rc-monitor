<?php

namespace Database\Factories;

use App\Models\DeviceCalendarEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceCalendarEvent>
 */
class DeviceCalendarEventFactory extends Factory
{
    protected $model = DeviceCalendarEvent::class;

    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-2 weeks', '+2 weeks');

        return [
            'title' => $this->faker->randomElement(['School pickup', 'Dentist appointment', 'Team meeting', 'Soccer practice', 'Doctor visit', 'Birthday party']),
            'location' => $this->faker->randomElement(['Main Street 12', 'Community Center', 'Office', 'School', null]),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+1 hour'),
            'source' => 'simulated',
        ];
    }
}
