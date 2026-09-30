<?php

namespace Database\Factories;

use App\Models\SimulationProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SimulationProfile>
 */
class SimulationProfileFactory extends Factory
{
    protected $model = SimulationProfile::class;

    public function definition(): array
    {
        return [
            'enabled' => true,
            'activity_level' => 'normal',
            'last_tick_at' => null,
        ];
    }

    public function disabled(): static
    {
        return $this->state(fn () => ['enabled' => false]);
    }

    public function level(string $level): static
    {
        return $this->state(fn () => ['activity_level' => $level]);
    }
}
