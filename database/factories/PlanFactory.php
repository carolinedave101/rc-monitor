<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'price_cents' => 4900,
            'currency' => 'USD',
            'billing_type' => 'one_time',
            'device_limit' => 5,
            'is_active' => true,
            'sort' => 0,
        ];
    }
}
