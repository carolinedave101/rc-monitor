<?php

namespace Database\Factories;

use App\Models\DeviceContact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceContact>
 */
class DeviceContactFactory extends Factory
{
    protected $model = DeviceContact::class;

    public function definition(): array
    {
        $name = $this->faker->name();

        return [
            'name' => $name,
            'phone_number' => '+1555'.$this->faker->numerify('#######'),
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.com',
            'source' => 'simulated',
        ];
    }
}
