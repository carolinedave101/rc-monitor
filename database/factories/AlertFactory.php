<?php

namespace Database\Factories;

use App\Models\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['keyword', 'geofence']);

        return [
            'type' => $type,
            'severity' => $this->faker->randomElement(['info', 'warning', 'critical']),
            'title' => $type === 'keyword'
                ? 'Keyword match: "'.$this->faker->randomElement(['help', 'urgent', 'address']).'"'
                : 'Device left safe zone',
            'body' => $this->faker->sentence(12),
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
