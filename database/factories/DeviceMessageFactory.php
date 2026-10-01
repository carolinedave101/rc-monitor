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
            'body' => $this->faker->randomElement([
                'Are you home yet?',
                'Call me when you can',
                'See you at dinner',
                'On my way home',
                'Running a bit late, sorry',
                'Can we study together tomorrow?',
                'Thank you so much',
                'Goodnight!',
                'Can you pick up milk on the way home?',
                'How was practice?',
            ]),
            'was_deleted' => false,
            'sent_at' => $this->faker->dateTimeThisMonth(),
        ];
    }
}
