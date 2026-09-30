<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\Consent;
use App\Models\Device;
use App\Models\DeviceCall;
use App\Models\DeviceLocation;
use App\Models\DeviceMessage;
use App\Models\DeviceShare;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\User;
use App\Services\SimulationEngine;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(FeatureSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(PaymentMethodSeeder::class);

        $user = User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $standard = Plan::where('code', 'standard')->first();

        if ($standard) {
            $user->update([
                'plan_id' => $standard->id,
                'plan_activated_at' => now()->subDays(10),
            ]);
        }

        $active = Device::factory()->for($user)->create([
            'name' => "Ava's Phone",
            'manufacturer' => 'Google',
            'model' => 'Pixel 8',
            'os' => 'android',
            'status' => 'active',
            'last_seen_at' => now(),
        ]);

        $active->simulationProfile()->create([
            'enabled' => true,
            'activity_level' => 'normal',
            'last_tick_at' => now()->subMinutes(15),
        ]);

        app(SimulationEngine::class)->backfill($active, 14);

        Device::factory()->for($user)->create([
            'name' => 'Work Tablet',
            'manufacturer' => 'Samsung',
            'model' => 'Galaxy Tab S9',
            'os' => 'android',
            'status' => 'pending',
            'last_seen_at' => null,
        ]);

        $suspended = Device::factory()->for($user)->create([
            'name' => "Mom's iPhone",
            'manufacturer' => 'Apple',
            'model' => 'iPhone 15',
            'os' => 'ios',
            'status' => 'suspended',
            'last_seen_at' => now()->subDays(3),
        ]);

        DeviceCall::factory()->count(12)->for($active)->create();
        DeviceMessage::factory()->count(20)->for($active)->create();
        DeviceLocation::factory()->count(10)->for($active)->create();

        DeviceCall::factory()->count(3)->for($suspended)->create();

        AlertRule::factory()->for($user)->keyword('help')->create([
            'device_id' => $active->id,
            'severity' => 'critical',
        ]);

        AlertRule::factory()->for($user)->geofence()->create([
            'device_id' => null,
            'severity' => 'warning',
            'enabled' => false,
        ]);

        Alert::factory()->for($user)->for($active)->count(4)->create();
        Alert::factory()->for($user)->for($active)->read()->create();

        $steps = [
            ['Account & consent', 'Registration completed and consent recorded.', 'completed'],
            ['Device enrollment & agent connection', 'Install the agent and connect the first device.', 'in_progress'],
            ['Baseline activity review', 'Walk through the first days of calls, messages and locations together.', 'pending'],
            ['Alerts configured & tuned', 'Set keyword and geofence rules that fit the family.', 'pending'],
            ['Ongoing monitoring & support', 'Regular check-ins and support for the account.', 'pending'],
        ];

        foreach ($steps as $index => [$title, $description, $status]) {
            $user->serviceSteps()->create([
                'title' => $title,
                'description' => $description,
                'position' => $index + 1,
                'status' => $status,
                'completed_at' => $status === 'completed' ? now()->subDays(6) : null,
            ]);
        }

        $partner = User::factory()->create([
            'name' => 'Partner Demo',
            'email' => 'partner@example.com',
        ]);

        $share = DeviceShare::factory()->for($active)->accepted()->create([
            'owner_id' => $user->id,
            'viewer_id' => $partner->id,
            'email' => $partner->email,
            'invited_by' => $user->id,
        ]);

        Consent::create([
            'user_id' => $partner->id,
            'device_id' => $active->id,
            'device_share_id' => $share->id,
            'type' => 'sharing',
            'method' => 'in_app',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'seeder',
            'consented_at' => now()->subDays(3),
        ]);

        $active->commands()->create([
            'requested_by' => $user->id,
            'type' => 'locate',
            'status' => 'acknowledged',
            'issued_at' => now()->subHours(2),
            'sent_at' => now()->subHours(2)->addMinutes(3),
            'acknowledged_at' => now()->subHours(2)->addMinutes(4),
            'result' => 'Location refreshed',
        ]);

        $methodIds = PaymentMethod::query()->pluck('id');
        $bankMethod = PaymentMethod::query()->where('type', 'bank_transfer')->first();

        $paidInvoice = Invoice::create([
            'user_id' => $user->id,
            'plan_id' => $standard?->id,
            'number' => 'INV-00001',
            'status' => 'paid',
            'subtotal_cents' => 4900,
            'total_cents' => 4900,
            'issued_at' => now()->subDays(10),
            'paid_at' => now()->subDays(9),
            'created_by' => $user->id,
        ]);

        $paidInvoice->items()->create([
            'description' => 'Standard plan — up to 5 devices',
            'quantity' => 1,
            'unit_price_cents' => 4900,
        ]);

        $paidInvoice->paymentMethods()->attach($methodIds);

        $paidInvoice->payments()->create([
            'user_id' => $user->id,
            'payment_method_id' => $bankMethod?->id,
            'amount_cents' => 4900,
            'reference' => 'BANK-88213',
            'status' => 'approved',
            'reviewed_by' => $user->id,
            'reviewed_at' => now()->subDays(9),
        ]);

        $reviewStep = $user->serviceSteps()->where('position', 3)->first();

        $openInvoice = Invoice::create([
            'user_id' => $user->id,
            'service_step_id' => $reviewStep?->id,
            'number' => 'INV-00002',
            'status' => 'sent',
            'subtotal_cents' => 9900,
            'total_cents' => 9900,
            'issued_at' => now()->subDays(2),
            'due_at' => now()->addDays(5),
            'notes' => 'Covers the guided baseline activity review session.',
            'created_by' => $user->id,
        ]);

        $openInvoice->items()->create([
            'description' => 'Guided baseline activity review',
            'quantity' => 1,
            'unit_price_cents' => 9900,
        ]);

        $openInvoice->paymentMethods()->attach($methodIds);
    }
}
