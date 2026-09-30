<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'action' => $this->faker->randomElement(['device.status.updated', 'feature.status.updated', 'step.paused']),
            'meta' => ['note' => $this->faker->sentence(6)],
            'ip_address' => $this->faker->ipv4(),
        ];
    }
}
