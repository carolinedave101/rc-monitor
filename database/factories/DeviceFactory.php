<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name()."'s Phone",
            'manufacturer' => $this->faker->randomElement(['Google', 'Samsung', 'Apple', 'OnePlus']),
            'model' => $this->faker->randomElement(['Pixel 8', 'Galaxy S24', 'iPhone 15', '12']),
            'os' => $this->faker->randomElement(['android', 'ios']),
            'os_version' => $this->faker->numerify('##'),
            'status' => 'active',
            'consent_recorded' => true,
            'consented_at' => now(),
            'agent_token' => Device::generateToken(),
        ];
    }
}
