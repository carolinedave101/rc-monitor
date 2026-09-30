<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceAppActivity;
use App\Models\DeviceBrowserHistory;
use App\Models\DeviceCalendarEvent;
use App\Models\DeviceCall;
use App\Models\DeviceContact;
use App\Models\DeviceDiagnostic;
use App\Models\DeviceEmail;
use App\Models\DeviceLocation;
use App\Models\DeviceMedia;
use App\Models\DeviceMessage;
use App\Models\DeviceNote;
use App\Notifications\CommandCompleted;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class SimulationEngine
{
    public function __construct(private readonly AlertEngine $alerts) {}

    private const LEVEL_MULTIPLIERS = ['low' => 0.5, 'normal' => 1.0, 'high' => 2.0];

    private const CONTACTS = [
        ['Mom', '+15550100001'],
        ['Dad', '+15550100002'],
        ['Grandma', '+15550100003'],
        ['Uncle Joe', '+15550100004'],
        ['Emma', '+15550100005'],
        ['Liam', '+15550100006'],
        ['Sofia', '+15550100007'],
        ['Noah', '+15550100008'],
        ['Coach Riley', '+15550100009'],
        ['Ms. Carter', '+15550100010'],
        ['Dr. Patel', '+15550100011'],
        ['Aunt May', '+15550100012'],
    ];

    private const APPS = [
        ['WhatsApp', 'com.whatsapp', 'social'],
        ['Instagram', 'com.instagram.android', 'social'],
        ['Chrome', 'com.android.chrome', 'browser'],
        ['YouTube', 'com.google.android.youtube', 'media'],
        ['Gmail', 'com.google.android.gm', 'productivity'],
        ['Google Maps', 'com.google.android.apps.maps', 'navigation'],
        ['Spotify', 'com.spotify.music', 'music'],
        ['Camera', 'com.android.camera', 'media'],
        ['Khan Academy', 'org.khanacademy.android', 'education'],
        ['Telegram', 'org.telegram.messenger', 'social'],
    ];

    private const SITES = [
        ['https://www.wikipedia.org', 'wikipedia.org', 'Wikipedia, the free encyclopedia'],
        ['https://www.khanacademy.org', 'khanacademy.org', 'Khan Academy | Free Online Courses'],
        ['https://www.bbc.com/news', 'bbc.com', 'BBC News'],
        ['https://www.youtube.com', 'youtube.com', 'YouTube'],
        ['https://www.espn.com', 'espn.com', 'ESPN: Sports News'],
        ['https://www.nasa.gov', 'nasa.gov', 'NASA'],
        ['https://www.nationalgeographic.com', 'nationalgeographic.com', 'National Geographic'],
        ['https://www.codecademy.com', 'codecademy.com', 'Learn to Code'],
    ];

    private const PLATFORMS = ['sms', 'sms', 'whatsapp', 'whatsapp', 'telegram', 'instagram'];

    private const MESSAGE_BODIES = [
        'Are you home yet?',
        "Don't forget practice at 5",
        'Call me when you can',
        'See you at dinner',
        'Did you finish the homework?',
        'On my way home',
        'Goodnight!',
        'Can we study together tomorrow?',
        'Running a bit late, sorry',
        'Happy birthday!',
        'Miss you',
        'Thank you so much',
    ];

    private const EMAIL_SUBJECTS = [
        'Weekly school newsletter',
        'Your order has shipped',
        'Practice schedule update',
        'Invoice for this month',
        'Photos from the weekend',
        'Reminder: dentist appointment',
    ];

    private const NOTE_TITLES = ['Shopping list', 'Homework', 'Project ideas', 'Packing list', 'Wish list'];

    private const CALENDAR_EVENTS = [
        ['School pickup', 'School'],
        ['Dentist appointment', 'Dental Clinic'],
        ['Team meeting', 'Office'],
        ['Soccer practice', 'Community Field'],
        ['Doctor visit', 'Medical Center'],
        ['Music lesson', 'Music School'],
    ];

    private const NETWORKS = ['wifi', 'wifi', '5g', '4g'];

    private const HOME = [51.5074, -0.1278];

    private const WORK = [51.5155, -0.0922];

    public function tickDevice(Device $device): array
    {
        $profile = $device->simulationProfile;

        if (! $profile || ! $profile->enabled) {
            return [];
        }

        $to = now();
        $from = $profile->last_tick_at ?? $to->copy()->subMinutes(15);

        if ($from->diffInMinutes($to) > 6 * 60) {
            $from = $to->copy()->subHours(6);
        }

        $counts = $this->generateBetween($device, $from, $to);

        $this->acknowledgeCommands($device);

        $profile->update(['last_tick_at' => $to]);

        return $counts;
    }

    protected function acknowledgeCommands(Device $device): void
    {
        $commands = $device->commands()
            ->whereIn('status', ['pending', 'sent'])
            ->get();

        foreach ($commands as $command) {
            $command->update([
                'status' => 'acknowledged',
                'result' => 'Acknowledged by simulated agent',
                'acknowledged_at' => now(),
            ]);

            $device->user?->notify(new CommandCompleted($command));
        }
    }

    public function backfill(Device $device, int $days = 30): array
    {
        $totals = [];

        for ($day = $days; $day >= 1; $day--) {
            $from = now()->subDays($day)->startOfDay();
            $to = now()->subDays($day)->endOfDay();

            foreach ($this->generateBetween($device, $from, $to) as $key => $count) {
                $totals[$key] = ($totals[$key] ?? 0) + $count;
            }
        }

        return $totals;
    }

    public function wipe(Device $device): array
    {
        $deleted = [];

        foreach ($this->domainModels() as $key => $model) {
            $deleted[$key] = $model::query()
                ->where('device_id', $device->id)
                ->where('source', 'simulated')
                ->delete();
        }

        return $deleted;
    }

    /**
     * @return array<string, class-string>
     */
    public function domainModels(): array
    {
        return [
            'calls' => DeviceCall::class,
            'messages' => DeviceMessage::class,
            'locations' => DeviceLocation::class,
            'apps' => DeviceAppActivity::class,
            'browser' => DeviceBrowserHistory::class,
            'emails' => DeviceEmail::class,
            'media' => DeviceMedia::class,
            'notes' => DeviceNote::class,
            'calendar' => DeviceCalendarEvent::class,
            'diagnostics' => DeviceDiagnostic::class,
        ];
    }

    protected function generateBetween(Device $device, CarbonInterface $from, CarbonInterface $to): array
    {
        $multiplier = self::LEVEL_MULTIPLIERS[$device->simulationProfile?->activity_level ?? 'normal'] ?? 1.0;
        $minutes = max(1.0, (float) $from->diffInMinutes($to));
        $scale = $multiplier * ($minutes / 1440);

        $this->ensureContacts($device);

        $counts = array_fill_keys(array_keys($this->domainModels()), 0);

        for ($i = 0, $n = $this->sample(8 * $scale); $i < $n; $i++) {
            [$name, $phone] = fake()->randomElement(self::CONTACTS);

            DeviceCall::create([
                'device_id' => $device->id,
                'direction' => fake()->randomElement(['incoming', 'outgoing', 'outgoing', 'missed']),
                'contact_name' => $name,
                'phone_number' => $phone,
                'duration_seconds' => fake()->numberBetween(0, 1800),
                'started_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $counts['calls']++;
        }

        for ($i = 0, $n = $this->sample(45 * $scale); $i < $n; $i++) {
            [$name, $phone] = fake()->randomElement(self::CONTACTS);

            $message = DeviceMessage::create([
                'device_id' => $device->id,
                'platform' => fake()->randomElement(self::PLATFORMS),
                'direction' => fake()->randomElement(['incoming', 'incoming', 'outgoing']),
                'contact_name' => $name,
                'phone_number' => $phone,
                'body' => fake()->randomElement(self::MESSAGE_BODIES),
                'was_deleted' => false,
                'sent_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $this->alerts->evaluateMessage($device, $message);

            $counts['messages']++;
        }

        for ($i = 0, $n = $this->sample(($minutes / 15) * $multiplier); $i < $n; $i++) {
            [$latitude, $longitude, $label] = $this->randomLocation();

            $location = DeviceLocation::create([
                'device_id' => $device->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => fake()->numberBetween(5, 60),
                'label' => $label,
                'recorded_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $this->alerts->evaluateLocation($device, $location);

            $counts['locations']++;
        }

        for ($i = 0, $n = $this->sample(30 * $scale); $i < $n; $i++) {
            [$appName, $package, $category] = fake()->randomElement(self::APPS);

            DeviceAppActivity::create([
                'device_id' => $device->id,
                'app_name' => $appName,
                'package' => $package,
                'category' => $category,
                'duration_seconds' => fake()->numberBetween(30, 3600),
                'launched_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $counts['apps']++;
        }

        for ($i = 0, $n = $this->sample(18 * $scale); $i < $n; $i++) {
            [$url, $domain, $title] = fake()->randomElement(self::SITES);

            DeviceBrowserHistory::create([
                'device_id' => $device->id,
                'url' => $url,
                'domain' => $domain,
                'title' => $title,
                'visited_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $counts['browser']++;
        }

        for ($i = 0, $n = $this->sample(7 * $scale); $i < $n; $i++) {
            DeviceEmail::create([
                'device_id' => $device->id,
                'direction' => fake()->randomElement(['incoming', 'incoming', 'outgoing']),
                'address' => fake()->safeEmail(),
                'subject' => fake()->randomElement(self::EMAIL_SUBJECTS),
                'snippet' => fake()->sentence(12),
                'sent_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $counts['emails']++;
        }

        for ($i = 0, $n = $this->sample(4 * $scale); $i < $n; $i++) {
            $isVideo = fake()->boolean(25);
            $takenAt = $this->randomTime($from, $to);

            DeviceMedia::create([
                'device_id' => $device->id,
                'type' => $isVideo ? 'video' : 'photo',
                'filename' => ($isVideo ? 'VID_' : 'IMG_').Carbon::parse($takenAt)->format('Ymd_His').($isVideo ? '.mp4' : '.jpg'),
                'size_mb' => fake()->randomFloat(2, 0.4, $isVideo ? 250 : 12),
                'taken_at' => $takenAt,
                'source' => 'simulated',
            ]);

            $counts['media']++;
        }

        for ($i = 0, $n = $this->sample(0.8 * $scale); $i < $n; $i++) {
            DeviceNote::create([
                'device_id' => $device->id,
                'title' => fake()->randomElement(self::NOTE_TITLES),
                'body' => fake()->sentence(10),
                'source' => 'simulated',
            ]);

            $counts['notes']++;
        }

        for ($i = 0, $n = $this->sample(1.2 * $scale); $i < $n; $i++) {
            [$title, $location] = fake()->randomElement(self::CALENDAR_EVENTS);
            $startsAt = $this->randomTime($from, $to);

            DeviceCalendarEvent::create([
                'device_id' => $device->id,
                'title' => $title,
                'location' => $location,
                'starts_at' => $startsAt,
                'ends_at' => Carbon::parse($startsAt)->addHour(),
                'source' => 'simulated',
            ]);

            $counts['calendar']++;
        }

        for ($i = 0, $n = $this->sample(($minutes / 60) * $multiplier); $i < $n; $i++) {
            DeviceDiagnostic::create([
                'device_id' => $device->id,
                'battery_percent' => fake()->numberBetween(15, 100),
                'is_charging' => fake()->boolean(20),
                'storage_used_mb' => fake()->numberBetween(40_000, 118_000),
                'storage_total_mb' => 128_000,
                'network' => fake()->randomElement(self::NETWORKS),
                'recorded_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $counts['diagnostics']++;
        }

        return $counts;
    }

    protected function ensureContacts(Device $device): void
    {
        if ($device->contacts()->exists()) {
            return;
        }

        foreach (self::CONTACTS as [$name, $phone]) {
            DeviceContact::create([
                'device_id' => $device->id,
                'name' => $name,
                'phone_number' => $phone,
                'email' => strtolower(str_replace([' ', '.'], ['.', ''], $name)).'@example.com',
                'source' => 'simulated',
            ]);
        }
    }

    /**
     * @return array{0: float, 1: float, 2: string}
     */
    protected function randomLocation(): array
    {
        $roll = random_int(0, 99);

        if ($roll < 45) {
            $anchor = self::HOME;
            $label = 'Home';
            $jitter = 0.008;
        } elseif ($roll < 80) {
            $anchor = self::WORK;
            $label = 'Work';
            $jitter = 0.01;
        } else {
            $anchor = [
                (self::HOME[0] + self::WORK[0]) / 2,
                (self::HOME[1] + self::WORK[1]) / 2,
            ];
            $label = 'In transit';
            $jitter = 0.03;
        }

        return [
            round($anchor[0] + (random_int(-1000, 1000) / 1000) * $jitter, 7),
            round($anchor[1] + (random_int(-1000, 1000) / 1000) * $jitter, 7),
            $label,
        ];
    }

    protected function sample(float $expected): int
    {
        if ($expected <= 0) {
            return 0;
        }

        $jittered = $expected * (random_int(60, 140) / 100);
        $count = (int) floor($jittered);

        if (random_int(1, 1000) <= (int) round(($jittered - $count) * 1000)) {
            $count++;
        }

        return $count;
    }

    protected function randomTime(CarbonInterface $from, CarbonInterface $to): CarbonInterface
    {
        $fromTs = $from->getTimestamp();
        $toTs = $to->getTimestamp();

        if ($toTs <= $fromTs) {
            return $from->copy();
        }

        return Carbon::createFromTimestamp(random_int($fromTs, $toTs));
    }
}
