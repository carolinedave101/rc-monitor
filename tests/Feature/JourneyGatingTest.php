<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\ServiceStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JourneyGatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_invoice_for_a_step_puts_it_awaiting_payment()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create([
            'status' => 'in_progress',
            'position' => 1,
            'requires_payment' => true,
        ]);
        $method = PaymentMethod::factory()->create();

        $this->actingAs($admin)
            ->post('/admin/invoices', [
                'user_id' => $customer->id,
                'service_step_id' => $step->id,
                'description' => 'Guided review session',
                'amount' => 99,
                'methods' => [$method->id],
            ])
            ->assertRedirect();

        $this->assertSame('awaiting_payment', $step->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'journey.step.awaiting_payment']);
    }

    public function test_uploading_proof_moves_the_step_to_awaiting_verification()
    {
        Storage::fake('local');

        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create([
            'status' => 'awaiting_payment',
            'requires_payment' => true,
        ]);
        $method = PaymentMethod::factory()->create();
        $invoice = Invoice::factory()->for($customer)->create(['service_step_id' => $step->id]);
        $invoice->paymentMethods()->attach($method->id);

        $this->actingAs($customer)
            ->post("/billing/invoices/{$invoice->id}/payments", [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->create('receipt.pdf', 120, 'application/pdf'),
            ])
            ->assertRedirect();

        $this->assertSame('awaiting_verification', $step->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'journey.step.awaiting_verification']);
    }

    public function test_approving_payment_completes_the_step_and_starts_the_next()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $next = ServiceStep::factory()->for($customer)->create(['position' => 2, 'status' => 'pending']);
        $step = ServiceStep::factory()->for($customer)->create([
            'position' => 1,
            'status' => 'awaiting_verification',
            'requires_payment' => true,
        ]);
        $method = PaymentMethod::factory()->create();
        $invoice = Invoice::factory()->for($customer)->awaitingVerification()->create(['service_step_id' => $step->id]);
        $invoice->paymentMethods()->attach($method->id);
        $payment = Payment::factory()->for($invoice)->for($customer)->create(['payment_method_id' => $method->id]);

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/approve")
            ->assertRedirect();

        $this->assertSame('completed', $step->fresh()->status);
        $this->assertNotNull($step->fresh()->completed_at);
        $this->assertSame('in_progress', $next->fresh()->status);
        $this->assertSame('paid', $invoice->fresh()->status);
    }

    public function test_rejecting_payment_returns_the_step_to_awaiting_payment()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create([
            'status' => 'awaiting_verification',
            'requires_payment' => true,
        ]);
        $invoice = Invoice::factory()->for($customer)->awaitingVerification()->create(['service_step_id' => $step->id]);
        $payment = Payment::factory()->for($invoice)->for($customer)->create();

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/reject", ['reason' => 'Unreadable receipt.'])
            ->assertRedirect();

        $this->assertSame('awaiting_payment', $step->fresh()->status);
        $this->assertSame('rejected', $invoice->fresh()->status);
    }

    public function test_voiding_an_invoice_returns_the_step_to_in_progress()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create([
            'status' => 'awaiting_payment',
            'requires_payment' => true,
        ]);
        $invoice = Invoice::factory()->for($customer)->create(['service_step_id' => $step->id]);

        $this->actingAs($admin)
            ->post("/admin/invoices/{$invoice->id}/void")
            ->assertRedirect();

        $this->assertSame('in_progress', $step->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'journey.step.payment_reverted']);
    }

    public function test_journey_page_shows_pay_link_for_the_outstanding_invoice()
    {
        $customer = User::factory()->create();
        $step = ServiceStep::factory()->for($customer)->create([
            'status' => 'awaiting_payment',
            'requires_payment' => true,
        ]);
        $invoice = Invoice::factory()->for($customer)->create(['service_step_id' => $step->id]);

        $this->actingAs($customer)
            ->get('/journey')
            ->assertOk()
            ->assertSee($invoice->number)
            ->assertSee(route('billing.show', $invoice));
    }
}
