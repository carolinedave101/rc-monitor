<?php

namespace Database\Factories;

use App\Models\DeviceAppActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceAppActivity>
 */
class DeviceAppActivityFactory extends Factory
{
    protected $model = DeviceAppActivity::class;

    public function definition(): array
    {
        $apps = [
            ['WhatsApp', 'com.whatsapp', 'social'],
            ['Instagram', 'com.instagram.android', 'social'],
            ['Chrome', 'com.android.chrome', 'browser'],
            ['YouTube', 'com.google.android.youtube', 'media'],
            ['Gmail', 'com.google.android.gm', 'productivity'],
            ['Maps', 'com.google.android.apps.maps', 'navigation'],
            ['Spotify', 'com.spotify.music', 'music'],
            ['Camera', 'com.android.camera', 'media'],
        ];

        [$appName, $package, $category] = $this->faker->randomElement($apps);

        return [
            'app_name' => $appName,
            'package' => $package,
            'category' => $category,
            'duration_seconds' => $this->faker->numberBetween(30, 3600),
            'launched_at' => $this->faker->dateTimeBetween('-1 month'),
            'source' => 'simulated',
        ];
    }
}
