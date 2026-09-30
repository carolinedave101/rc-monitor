<?php

namespace Database\Factories;

use App\Models\DeviceMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceMedia>
 */
class DeviceMediaFactory extends Factory
{
    protected $model = DeviceMedia::class;

    public function definition(): array
    {
        $isVideo = $this->faker->boolean(25);
        $stamp = $this->faker->dateTimeBetween('-1 month')->format('Ymd_His');

        return [
            'type' => $isVideo ? 'video' : 'photo',
            'filename' => ($isVideo ? 'VID_' : 'IMG_').$stamp.($isVideo ? '.mp4' : '.jpg'),
            'size_mb' => $this->faker->randomFloat(2, 0.4, $isVideo ? 250 : 12),
            'taken_at' => $this->faker->dateTimeBetween('-1 month'),
            'source' => 'simulated',
        ];
    }
}
