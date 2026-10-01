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

    /**
     * Contacts with a relative contact frequency (family texts more than the dentist).
     */
    private const CONTACTS = [
        ['Mom', '+15550100001', 12],
        ['Dad', '+15550100002', 11],
        ['Grandma', '+15550100003', 6],
        ['Uncle Joe', '+15550100004', 4],
        ['Emma', '+15550100005', 10],
        ['Liam', '+15550100006', 9],
        ['Sofia', '+15550100007', 8],
        ['Noah', '+15550100008', 8],
        ['Coach Riley', '+15550100009', 3],
        ['Ms. Carter', '+15550100010', 2],
        ['Dr. Patel', '+15550100011', 1],
        ['Aunt May', '+15550100012', 4],
        ['Cousin Ben', '+15550100013', 5],
        ['Priya', '+15550100014', 7],
        ['Marcus', '+15550100015', 6],
    ];

    /**
     * [name, package, category, min duration, max duration].
     */
    private const APPS = [
        ['WhatsApp', 'com.whatsapp', 'social', 60, 1500],
        ['Instagram', 'com.instagram.android', 'social', 90, 2400],
        ['Chrome', 'com.android.chrome', 'browser', 45, 1800],
        ['YouTube', 'com.google.android.youtube', 'media', 120, 3600],
        ['Gmail', 'com.google.android.gm', 'productivity', 40, 900],
        ['Google Maps', 'com.google.android.apps.maps', 'navigation', 60, 1200],
        ['Spotify', 'com.spotify.music', 'music', 300, 5400],
        ['Camera', 'com.android.camera', 'media', 20, 300],
        ['Khan Academy', 'org.khanacademy.android', 'education', 180, 2700],
        ['Telegram', 'org.telegram.messenger', 'social', 45, 1200],
        ['Duolingo', 'com.duolingo', 'education', 120, 1200],
        ['Netflix', 'com.netflix.mediaclient', 'media', 600, 7200],
        ['Snapchat', 'com.snapchat.android', 'social', 60, 1500],
        ['Google Classroom', 'com.google.android.apps.classroom', 'education', 90, 1500],
        ['Discord', 'com.discord', 'social', 120, 2700],
        ['Clock', 'com.google.android.deskclock', 'utility', 15, 120],
    ];

    private const SITES = [
        ['https://www.google.com/search?q=algebra+homework+help', 'google.com', 'algebra homework help - Google Search'],
        ['https://www.google.com/search?q=cheap+flights+to+dublin', 'google.com', 'cheap flights to dublin - Google Search'],
        ['https://www.youtube.com/watch?v=study-with-me-lofi', 'youtube.com', 'study with me | lofi beats - YouTube'],
        ['https://www.youtube.com/results?search_query=soccer+highlights', 'youtube.com', 'soccer highlights - YouTube'],
        ['https://www.khanacademy.org/math/algebra', 'khanacademy.org', 'Algebra 1 | Khan Academy'],
        ['https://www.bbc.com/news/technology', 'bbc.com', 'Technology | BBC News'],
        ['https://www.wikipedia.org/wiki/Photosynthesis', 'wikipedia.org', 'Photosynthesis - Wikipedia'],
        ['https://www.espn.com/soccer/standings', 'espn.com', 'Soccer Standings - ESPN'],
        ['https://www.instagram.com/explore/tags/hiking/', 'instagram.com', '#hiking on Instagram'],
        ['https://www.reddit.com/r/learnprogramming/', 'reddit.com', 'learnprogramming - Reddit'],
        ['https://www.amazon.com/s?k=graphing+calculator', 'amazon.com', 'graphing calculator - Amazon'],
        ['https://www.spotify.com/playlist/daily-mix', 'spotify.com', 'Daily Mix - Spotify'],
        ['https://www.nationalgeographic.com/animals', 'nationalgeographic.com', 'Animals - National Geographic'],
        ['https://mail.google.com/mail/u/0/#inbox', 'mail.google.com', 'Inbox - Gmail'],
        ['https://www.codecademy.com/learn/learn-python-3', 'codecademy.com', 'Learn Python 3 | Codecademy'],
    ];

    private const PLATFORMS = ['sms', 'sms', 'whatsapp', 'whatsapp', 'telegram', 'instagram'];

    /**
     * Message pools keyed by time of day; "any" is always eligible.
     */
    private const MESSAGES = [
        'morning' => [
            'Morning! Did you sleep ok?',
            'Leaving now, see you at the bus stop',
            "Don't forget your lunch",
            'Have a good day at school',
            'On the bus, be there in 10',
            'Can you pick up milk on the way home?',
            'Running late, start without me',
            'Good luck on the test today!',
        ],
        'day' => [
            'Are you home yet?',
            "Don't forget practice at 5",
            'Call me when you can',
            'Did you finish the homework?',
            'Can we study together tomorrow?',
            'Where are you?',
            'Lunch is at 12:30 right?',
            'I left my charger at home, can you bring it?',
            'What time does the movie start?',
            'Just got out of class',
        ],
        'evening' => [
            'See you at dinner',
            'On my way home',
            'Running a bit late, sorry',
            'Can I stay at Emma\'s tonight?',
            'Dinner is ready',
            'Can you grab the washing in?',
            'How was practice?',
            'Movie night?',
            'Do you need anything from the shop?',
        ],
        'night' => [
            'Goodnight!',
            'Heading to bed, love you',
            'Phone on charge please',
            'See you in the morning',
            'Last episode then sleep, promise',
            'Sweet dreams',
        ],
        'any' => [
            'Thank you so much',
            'Miss you',
            'Happy birthday!',
            'That sounds great',
            'Ok cool',
            'Haha yes',
            'Can you send me the address?',
            'I\'ll let you know',
            'Sounds good, see you then',
            'Got it, thanks',
        ],
    ];

    /**
     * [subject, address, snippet].
     */
    private const EMAILS = [
        ['Weekly school newsletter', 'office@oakwoodhigh.example', 'Parent-teacher conferences are Thursday; science fair sign-up closes Friday.'],
        ['Your order has shipped', 'orders@northwind.example', 'Order #48213 is on its way. Estimated delivery is Thursday before 6pm.'],
        ['Practice schedule update', 'coach.riley@oakwoodhigh.example', 'Thursday practice moves to 5:30 at the community field due to the weather.'],
        ['Invoice for this month', 'billing@cityutilities.example', 'Your invoice of $84.20 is due on the 28th. View it in your account.'],
        ['Photos from the weekend', 'aunt.may@example.com', 'Finally sorted the photos from the lake trip — the group shot is my favourite.'],
        ['Reminder: dentist appointment', 'reception@brightsmile.example', 'This is a reminder for your appointment on Tuesday at 10:00 with Dr. Patel.'],
        ['Your subscription renews soon', 'support@streamly.example', 'Your monthly plan renews on the 12th. You can manage it in account settings.'],
        ['Football club newsletter', 'news@oakwoodfc.example', 'Match report: a 3-2 win away at Riverside. Training photos inside.'],
        ['Library book due soon', 'notices@citylibrary.example', 'The book you borrowed is due in 3 days. Renew online to avoid a fine.'],
        ['Bank statement available', 'statements@firstbridge.example', 'Your monthly statement is ready to download from online banking.'],
    ];

    /**
     * [title, body].
     */
    private const NOTES = [
        ['Shopping list', "Milk\nBread\nBananas\nPasta\nDog food"],
        ['Homework', "Maths: questions 1-12 p.84\nEnglish: read chapter 5\nScience: label the diagram"],
        ['Project ideas', "Solar phone charger\nReusable water bottle tracker\nStudy playlist app"],
        ['Packing list', "Trainers\nWater bottle\nHoodie\nPhone charger\nSnacks"],
        ['Wish list', "Wireless earbuds\nSkateboard\nConcert tickets\nNew backpack"],
        ['Recipes to try', "Overnight oats\nChicken traybake\nBanana pancakes"],
        ['Revision plan', "Mon: algebra\nTue: biology\nWed: history\nThu: practice paper\nFri: review"],
        ['Books to read', "The Hobbit\nThe Hate U Give\nProject Hail Mary"],
    ];

    /**
     * [title, location, hour, minute, duration minutes].
     */
    private const CALENDAR_EVENTS = [
        ['School pickup', 'School', 15, 30, 45],
        ['Dentist appointment', 'Dental Clinic', 10, 0, 60],
        ['Team meeting', 'Office', 10, 0, 60],
        ['Soccer practice', 'Community Field', 17, 0, 90],
        ['Doctor visit', 'Medical Center', 9, 30, 45],
        ['Music lesson', 'Music School', 16, 0, 45],
        ['Parent evening', 'School', 18, 30, 90],
        ['Swimming lesson', 'Leisure Centre', 18, 0, 60],
        ['Study group', 'City Library', 16, 30, 120],
        ['Family dinner', 'Grandma\'s', 19, 0, 120],
    ];

    private const NETWORKS = ['wifi', 'wifi', 'wifi', '5g', '4g'];

    private const HOME = [51.5074, -0.1278];

    private const WORK = [51.5155, -0.0922];

    private const SCHOOL = [51.5121, -0.1042];

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

        $device->forceFill(['last_seen_at' => $to])->saveQuietly();

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
            [$name, $phone] = $this->pickContact();
            $direction = fake()->randomElement(['incoming', 'outgoing', 'outgoing', 'missed']);

            DeviceCall::create([
                'device_id' => $device->id,
                'direction' => $direction,
                'contact_name' => $name,
                'phone_number' => $phone,
                'duration_seconds' => $this->callDuration($direction),
                'started_at' => $this->randomTime($from, $to),
                'source' => 'simulated',
            ]);

            $counts['calls']++;
        }

        for ($i = 0, $n = $this->sample(45 * $scale); $i < $n; $i++) {
            [$name, $phone] = $this->pickContact();
            $sentAt = $this->randomTime($from, $to);

            $message = DeviceMessage::create([
                'device_id' => $device->id,
                'platform' => fake()->randomElement(self::PLATFORMS),
                'direction' => fake()->randomElement(['incoming', 'incoming', 'outgoing']),
                'contact_name' => $name,
                'phone_number' => $phone,
                'body' => $this->messageBody($sentAt),
                'was_deleted' => fake()->boolean(3),
                'sent_at' => $sentAt,
                'source' => 'simulated',
            ]);

            $this->alerts->evaluateMessage($device, $message);

            $counts['messages']++;
        }

        for ($i = 0, $n = $this->sample(($minutes / 15) * $multiplier); $i < $n; $i++) {
            $recordedAt = $this->randomTime($from, $to);
            [$latitude, $longitude, $label] = $this->randomLocation($recordedAt);

            $location = DeviceLocation::create([
                'device_id' => $device->id,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'accuracy_meters' => fake()->numberBetween(5, 60),
                'label' => $label,
                'recorded_at' => $recordedAt,
                'source' => 'simulated',
            ]);

            $this->alerts->evaluateLocation($device, $location);

            $counts['locations']++;
        }

        for ($i = 0, $n = $this->sample(30 * $scale); $i < $n; $i++) {
            [$appName, $package, $category, $minDuration, $maxDuration] = fake()->randomElement(self::APPS);

            DeviceAppActivity::create([
                'device_id' => $device->id,
                'app_name' => $appName,
                'package' => $package,
                'category' => $category,
                'duration_seconds' => $this->appDuration($appName, $minDuration, $maxDuration),
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
            [$subject, $address, $snippet] = fake()->randomElement(self::EMAILS);

            DeviceEmail::create([
                'device_id' => $device->id,
                'direction' => fake()->randomElement(['incoming', 'incoming', 'incoming', 'outgoing']),
                'address' => $address,
                'subject' => $subject,
                'snippet' => $snippet,
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
                'size_mb' => $isVideo
                    ? fake()->randomFloat(2, 18, 320)
                    : fake()->randomFloat(2, 1.2, 9.5),
                'taken_at' => $takenAt,
                'source' => 'simulated',
            ]);

            $counts['media']++;
        }

        for ($i = 0, $n = $this->sample(0.8 * $scale); $i < $n; $i++) {
            [$title, $body] = fake()->randomElement(self::NOTES);

            DeviceNote::create([
                'device_id' => $device->id,
                'title' => $title,
                'body' => $body,
                'source' => 'simulated',
            ]);

            $counts['notes']++;
        }

        foreach ($this->calendarEvents($from, $to, $this->sample(1.2 * $scale)) as $event) {
            DeviceCalendarEvent::create([
                'device_id' => $device->id,
                'title' => $event['title'],
                'location' => $event['location'],
                'starts_at' => $event['starts_at'],
                'ends_at' => $event['ends_at'],
                'source' => 'simulated',
            ]);

            $counts['calendar']++;
        }

        foreach ($this->diagnostics($device, $from, $to, $multiplier) as $diagnostic) {
            DeviceDiagnostic::create($diagnostic);
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
                'email' => strtolower(str_replace([' ', "'"], ['.', ''], $name)).'@example.com',
                'source' => 'simulated',
            ]);
        }
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function pickContact(): array
    {
        $total = array_sum(array_column(self::CONTACTS, 2));
        $roll = random_int(1, $total);

        foreach (self::CONTACTS as [$name, $phone, $weight]) {
            $roll -= $weight;

            if ($roll <= 0) {
                return [$name, $phone];
            }
        }

        return [self::CONTACTS[0][0], self::CONTACTS[0][1]];
    }

    protected function callDuration(string $direction): int
    {
        if ($direction === 'missed') {
            return 0;
        }

        $roll = random_int(1, 100);

        return match (true) {
            $roll <= 65 => random_int(15, 420),
            $roll <= 92 => random_int(421, 1200),
            default => random_int(1201, 2700),
        };
    }

    protected function appDuration(string $appName, int $min, int $max): int
    {
        $duration = random_int($min, $max);

        if (in_array($appName, ['Spotify', 'Netflix'], true)) {
            return $duration;
        }

        return (int) round($duration * (random_int(70, 130) / 100));
    }

    protected function messageBody(CarbonInterface $at): string
    {
        $hour = (int) $at->format('G');

        $bucket = match (true) {
            $hour >= 5 && $hour < 11 => 'morning',
            $hour >= 11 && $hour < 17 => 'day',
            $hour >= 17 && $hour < 22 => 'evening',
            default => 'night',
        };

        return fake()->randomElement(array_merge(self::MESSAGES[$bucket], self::MESSAGES['any']));
    }

    /**
     * @return array<int, array{title: string, location: string, starts_at: CarbonInterface, ends_at: CarbonInterface}>
     */
    protected function calendarEvents(CarbonInterface $from, CarbonInterface $to, int $count): array
    {
        $events = [];

        for ($i = 0; $i < $count; $i++) {
            [$title, $location, $hour, $minute, $duration] = fake()->randomElement(self::CALENDAR_EVENTS);

            $startsAt = $from->copy()->setTime($hour, $minute);

            if ($startsAt->lt($from) || $startsAt->gt($to)) {
                continue;
            }

            $events[] = [
                'title' => $title,
                'location' => $location,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes($duration),
            ];
        }

        return $events;
    }

    /**
     * Battery drains and charges as a random walk; storage grows monotonically.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function diagnostics(Device $device, CarbonInterface $from, CarbonInterface $to, float $multiplier): array
    {
        $count = $this->sample(((float) $from->diffInMinutes($to) / 60) * $multiplier);

        if ($count === 0) {
            return [];
        }

        $fromTs = $from->getTimestamp();
        $toTs = $to->getTimestamp();
        $hours = max(0.1, (float) $from->diffInMinutes($to) / 60);
        $stepHours = $hours / $count;

        $last = DeviceDiagnostic::query()
            ->where('device_id', $device->id)
            ->orderByDesc('recorded_at')
            ->first();

        $battery = $last?->battery_percent ?? random_int(35, 90);
        $charging = $last?->is_charging ?? fake()->boolean(20);
        $storageUsed = $last?->storage_used_mb ?? random_int(52_000, 72_000);
        $storageTotal = 128_000;

        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $progress = ($i + 0.5) / $count;
            $recordedAt = Carbon::createFromTimestamp((int) ($fromTs + ($toTs - $fromTs) * $progress))
                ->addSeconds(random_int(-240, 240));

            if ($recordedAt->lt($from)) {
                $recordedAt = $from->copy();
            }

            if ($recordedAt->gt($to)) {
                $recordedAt = $to->copy();
            }

            if ($charging) {
                $battery = min(100, $battery + (int) max(1, round(random_int(6, 22) * $stepHours)));

                if ($battery >= 100) {
                    $charging = fake()->boolean(30);
                } elseif (fake()->boolean(10)) {
                    $charging = false;
                }
            } else {
                $battery = max(1, $battery - (int) max(1, round(random_int(2, 7) * $stepHours)));

                if ($battery <= 15 || fake()->boolean(6)) {
                    $charging = true;
                }
            }

            $storageUsed = min(
                (int) ($storageTotal * 0.96),
                $storageUsed + (int) round((random_int(30, 120) / 24) * $stepHours),
            );

            $rows[] = [
                'device_id' => $device->id,
                'battery_percent' => $battery,
                'is_charging' => $charging,
                'storage_used_mb' => $storageUsed,
                'storage_total_mb' => $storageTotal,
                'network' => fake()->randomElement(self::NETWORKS),
                'recorded_at' => $recordedAt,
                'source' => 'simulated',
            ];
        }

        return $rows;
    }

    /**
     * @return array{0: float, 1: float, 2: string}
     */
    protected function randomLocation(?CarbonInterface $at = null): array
    {
        $hour = $at ? (int) $at->format('G') : random_int(0, 23);

        $roll = random_int(1, 100);

        if ($hour >= 23 || $hour < 6) {
            [$anchor, $label, $jitter] = $roll <= 85
                ? [self::HOME, 'Home', 0.006]
                : [self::WORK, 'Work', 0.006];
        } elseif ($hour >= 9 && $hour < 17) {
            [$anchor, $label, $jitter] = match (true) {
                $roll <= 50 => [self::WORK, 'Work', 0.008],
                $roll <= 70 => [self::SCHOOL, 'School', 0.008],
                $roll <= 85 => [null, 'In transit', 0],
                default => [self::HOME, 'Home', 0.006],
            };
        } elseif (($hour >= 7 && $hour < 9) || ($hour >= 17 && $hour < 19)) {
            [$anchor, $label, $jitter] = $roll <= 65
                ? [null, 'In transit', 0]
                : [self::HOME, 'Home', 0.006];
        } else {
            [$anchor, $label, $jitter] = match (true) {
                $roll <= 55 => [self::HOME, 'Home', 0.007],
                $roll <= 75 => [null, 'In transit', 0],
                $roll <= 90 => [self::WORK, 'Work', 0.008],
                default => [self::SCHOOL, 'School', 0.008],
            };
        }

        if ($anchor === null) {
            return $this->transitLocation();
        }

        return [
            round($anchor[0] + (random_int(-1000, 1000) / 1000) * $jitter, 7),
            round($anchor[1] + (random_int(-1000, 1000) / 1000) * $jitter, 7),
            $label,
        ];
    }

    /**
     * A point along the home-work corridor with a gentle curve, not a straight midpoint.
     *
     * @return array{0: float, 1: float, 2: string}
     */
    protected function transitLocation(): array
    {
        $t = random_int(50, 950) / 1000;

        $latitude = self::HOME[0] + (self::WORK[0] - self::HOME[0]) * $t;
        $longitude = self::HOME[1] + (self::WORK[1] - self::HOME[1]) * $t;

        $latitude += (random_int(-1000, 1000) / 1000) * 0.006;
        $longitude += (random_int(-1000, 1000) / 1000) * 0.006;

        return [round($latitude, 7), round($longitude, 7), 'In transit'];
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

    /**
     * Pick the most "active" of a few random candidates, weighted by time of day.
     */
    protected function randomTime(CarbonInterface $from, CarbonInterface $to): CarbonInterface
    {
        $fromTs = $from->getTimestamp();
        $toTs = $to->getTimestamp();

        if ($toTs <= $fromTs) {
            return $from->copy();
        }

        $best = null;
        $bestScore = -1.0;

        for ($i = 0; $i < 12; $i++) {
            $candidate = Carbon::createFromTimestamp(random_int($fromTs, $toTs));
            $score = $this->timeWeight($candidate) * (random_int(1, 1000) / 1000);

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $candidate;
            }
        }

        return $best ?? Carbon::createFromTimestamp(random_int($fromTs, $toTs));
    }

    protected function timeWeight(CarbonInterface $time): float
    {
        $hour = (int) $time->format('G');

        $weekday = [
            0 => 0.04, 1 => 0.03, 2 => 0.03, 3 => 0.03, 4 => 0.04, 5 => 0.10,
            6 => 0.35, 7 => 0.85, 8 => 1.00, 9 => 0.75, 10 => 0.70, 11 => 0.70,
            12 => 0.95, 13 => 0.85, 14 => 0.70, 15 => 0.75, 16 => 0.85, 17 => 1.00,
            18 => 0.95, 19 => 0.90, 20 => 0.80, 21 => 0.65, 22 => 0.35, 23 => 0.15,
        ];

        $weekend = [
            0 => 0.10, 1 => 0.06, 2 => 0.04, 3 => 0.03, 4 => 0.03, 5 => 0.04,
            6 => 0.08, 7 => 0.20, 8 => 0.40, 9 => 0.65, 10 => 0.85, 11 => 0.95,
            12 => 1.00, 13 => 0.95, 14 => 0.90, 15 => 0.90, 16 => 0.85, 17 => 0.80,
            18 => 0.75, 19 => 0.70, 20 => 0.60, 21 => 0.45, 22 => 0.30, 23 => 0.20,
        ];

        return ($time->isWeekend() ? $weekend : $weekday)[$hour];
    }
}
