<?php

namespace Database\Factories;

use App\Models\DeviceBrowserHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceBrowserHistory>
 */
class DeviceBrowserHistoryFactory extends Factory
{
    protected $model = DeviceBrowserHistory::class;

    public function definition(): array
    {
        $sites = [
            ['https://www.wikipedia.org', 'wikipedia.org', 'Wikipedia, the free encyclopedia'],
            ['https://www.khanacademy.org', 'khanacademy.org', 'Khan Academy | Free Online Courses'],
            ['https://www.bbc.com/news', 'bbc.com', 'BBC News'],
            ['https://www.youtube.com', 'youtube.com', 'YouTube'],
            ['https://www.espn.com', 'espn.com', 'ESPN: Sports News'],
            ['https://www.nasa.gov', 'nasa.gov', 'NASA'],
            ['https://www.nationalgeographic.com', 'nationalgeographic.com', 'National Geographic'],
        ];

        [$url, $domain, $title] = $this->faker->randomElement($sites);

        return [
            'url' => $url,
            'domain' => $domain,
            'title' => $title,
            'visited_at' => $this->faker->dateTimeBetween('-1 month'),
            'source' => 'simulated',
        ];
    }
}
