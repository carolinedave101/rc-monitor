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
        $keyword = $this->faker->randomElement(['help', 'urgent', 'address']);

        return [
            'type' => $type,
            'severity' => $this->faker->randomElement(['info', 'warning', 'critical']),
            'title' => $type === 'keyword'
                ? 'Keyword match: "'.$keyword.'"'
                : 'Device left safe zone',
            'body' => $type === 'keyword'
                ? 'A message on this device matched the keyword "'.$keyword.'". Review the conversation in the Messages tab.'
                : 'The device moved outside the configured safe zone. Check the latest locations on the device page.',
            'read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state(fn () => ['read_at' => now()]);
    }
}
