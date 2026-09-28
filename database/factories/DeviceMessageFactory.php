<?php

namespace Database\Factories;

use App\Models\DeviceMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceMessage>
 */
class DeviceMessageFactory extends Factory
{
    protected $model = DeviceMessage::class;

    public function definition(): array
    {
        return [
            'platform' => $this->faker->randomElement(['sms', 'whatsapp', 'telegram']),
            'direction' => $this->faker->randomElement(['incoming', 'outgoing']),
            'contact_name' => $this->faker->name(),
            'phone_number' => '+1555'.$this->faker->numerify('#######'),
            'body' => $this->faker->sentence(),
            'was_deleted' => false,
            'sent_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
