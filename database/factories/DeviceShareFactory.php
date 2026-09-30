<?php

namespace Database\Factories;

use App\Models\DeviceShare;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceShare>
 */
class DeviceShareFactory extends Factory
{
    protected $model = DeviceShare::class;

    public function definition(): array
    {
        return [
            'email' => fake()->safeEmail(),
            'status' => 'pending',
            'owner_id' => null,
            'viewer_id' => null,
            'invited_by' => null,
        ];
    }

    public function accepted(): static
    {
        return $this->state(fn () => [
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function revoked(): static
    {
        return $this->state(fn () => [
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);
    }
}
