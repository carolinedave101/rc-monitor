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
        $os = $this->faker->randomElement(['android', 'ios']);

        return [
            'name' => $this->faker->name()."'s Phone",
            'manufacturer' => $this->faker->randomElement(['Google', 'Samsung', 'Apple', 'OnePlus']),
            'model' => $this->faker->randomElement(['Pixel 8', 'Galaxy S24', 'iPhone 15', '12']),
            'os' => $os,
            'os_version' => $os === 'android'
                ? $this->faker->randomElement(['13', '14', '15'])
                : $this->faker->randomElement(['16.6', '17.4', '18.1']),
            'status' => 'active',
            'consent_recorded' => true,
            'consented_at' => now(),
            'agent_token' => Device::generateToken(),
        ];
    }
}
