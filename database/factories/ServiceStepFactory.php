<?php

namespace Database\Factories;

use App\Models\ServiceStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceStep>
 */
class ServiceStepFactory extends Factory
{
    protected $model = ServiceStep::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'position' => 0,
            'status' => 'pending',
            'requires_payment' => false,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => 'in_progress']);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed', 'completed_at' => now()]);
    }
}
