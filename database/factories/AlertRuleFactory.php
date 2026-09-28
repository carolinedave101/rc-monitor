<?php

namespace Database\Factories;

use App\Models\AlertRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertRule>
 */
class AlertRuleFactory extends Factory
{
    protected $model = AlertRule::class;

    public function definition(): array
    {
        return [
            'device_id' => null,
            'type' => 'keyword',
            'keyword' => $this->faker->randomElement(['help', 'please', 'urgent']),
            'severity' => $this->faker->randomElement(['info', 'warning', 'critical']),
            'enabled' => true,
        ];
    }

    public function keyword(string $keyword): static
    {
        return $this->state(fn () => ['type' => 'keyword', 'keyword' => $keyword]);
    }

    public function geofence(): static
    {
        return $this->state(fn () => [
            'type' => 'geofence',
            'latitude' => 51.5074,
            'longitude' => -0.1278,
            'radius_meters' => 1000,
        ]);
    }
}
