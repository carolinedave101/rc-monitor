<?php

namespace Database\Factories;

use App\Models\Consent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consent>
 */
class ConsentFactory extends Factory
{
    protected $model = Consent::class;

    public function definition(): array
    {
        return [
            'type' => 'sharing',
            'method' => 'in_app',
            'ip_address' => fake()->ipv4(),
            'consented_at' => now(),
        ];
    }
}
