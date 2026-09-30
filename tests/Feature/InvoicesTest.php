<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Notifications\PaymentSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoicesTest extends TestCase
{
    use RefreshDatabase;

    protected function invoiceFor(User $user, array $attributes = []): Invoice
    {
        $invoice = Invoice::factory()->for($user)->create($attributes);

        $invoice->items()->create([
            'description' => 'Standard plan',
            'quantity' => 1,
            'unit_price_cents' => $invoice->total_cents,
        ]);

        $invoice->paymentMethods()->attach(
            PaymentMethod::factory()->count(2)->create()->pluck('id'),
        );

        return $invoice->fresh(['items', 'paymentMethods']);
    }

    public function test_billing_page_lists_only_own_invoices()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = $this->invoiceFor($user);
        $theirs = $this->invoiceFor($other);

        $this->actingAs($user)
            ->get('/billing')
            ->assertOk()
            ->assertSee($mine->number)
            ->assertDontSee($theirs->number);
    }

    public function test_customer_can_view_own_invoice_only()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $invoice = $this->invoiceFor($user);
        $foreign = $this->invoiceFor($other);

        $this->actingAs($user)
            ->get("/billing/invoices/{$invoice->id}")
            ->assertOk()
            ->assertSee($invoice->number)
            ->assertSee('How to pay');

        $this->actingAs($user)
            ->get("/billing/invoices/{$foreign->id}")
            ->assertForbidden();
    }

    public function test_customer_can_submit_proof_of_payment()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $invoice = $this->invoiceFor($user);
        $method = $invoice->paymentMethods->first();

        $this->actingAs($user)
            ->post("/billing/invoices/{$invoice->id}/payments", [
                'payment_method_id' => $method->id,
                'reference' => 'BANK-12345',
                'proof' => UploadedFile::fake()->create('receipt.jpg', 120, 'image/jpeg'),
            ])
            ->assertRedirect();

        $this->assertSame('awaiting_verification', $invoice->fresh()->status);

        $payment = Payment::first();
        $this->assertSame('pending_verification', $payment->status);
        $this->assertSame($user->id, $payment->user_id);
        $this->assertSame('BANK-12345', $payment->reference);
        $this->assertNotNull($payment->proof_path);
        Storage::disk('local')->assertExists($payment->proof_path);

        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.submitted']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'type' => PaymentSubmitted::class,
        ]);
    }

    public function test_proof_upload_is_validated()
    {
        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);
        $method = $invoice->paymentMethods->first();

        $this->actingAs($user)
            ->post("/billing/invoices/{$invoice->id}/payments", [
                'payment_method_id' => $method->id,
            ])
            ->assertSessionHasErrors('proof');

        $this->assertSame('sent', $invoice->fresh()->status);
        $this->assertSame(0, Payment::count());
    }

    public function test_cannot_submit_a_method_that_is_not_on_the_invoice()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user);
        $unrelated = PaymentMethod::factory()->create();

        $this->actingAs($user)
            ->post("/billing/invoices/{$invoice->id}/payments", [
                'payment_method_id' => $unrelated->id,
                'proof' => UploadedFile::fake()->create('receipt.jpg', 120, 'image/jpeg'),
            ])
            ->assertStatus(422);

        $this->assertSame('sent', $invoice->fresh()->status);
    }

    public function test_paid_invoice_cannot_accept_new_payments()
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $invoice = $this->invoiceFor($user, ['status' => 'paid', 'paid_at' => now()]);
        $method = $invoice->paymentMethods->first();

        $this->actingAs($user)
            ->post("/billing/invoices/{$invoice->id}/payments", [
                'payment_method_id' => $method->id,
                'proof' => UploadedFile::fake()->create('receipt.jpg', 120, 'image/jpeg'),
            ])
            ->assertStatus(422);

        $this->assertSame(0, Payment::count());
    }
}
