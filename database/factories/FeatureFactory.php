<?php

namespace Database\Factories;

use App\Models\Feature;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feature>
 */
class FeatureFactory extends Factory
{
    protected $model = Feature::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->slug(2),
            'name' => $this->faker->words(2, true),
            'category' => $this->faker->randomElement(['activity', 'safety', 'social', 'device', 'platform']),
            'description' => $this->faker->sentence(),
            'status' => 'coming_soon',
            'is_public' => true,
            'sort' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
