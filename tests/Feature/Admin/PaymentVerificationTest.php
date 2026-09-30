<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PaymentApproved;
use App\Notifications\PaymentRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function pendingPayment(User $customer, array $invoiceAttributes = []): Payment
    {
        $invoice = Invoice::factory()->for($customer)->awaitingVerification()->create($invoiceAttributes);
        $method = PaymentMethod::factory()->create();
        $invoice->paymentMethods()->attach($method->id);

        return Payment::factory()->for($invoice)->for($customer)->create([
            'payment_method_id' => $method->id,
            'proof_path' => 'payment-proofs/receipt.jpg',
            'original_filename' => 'receipt.jpg',
        ]);
    }

    public function test_non_admin_cannot_access_the_queue()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/payments')->assertForbidden();
    }

    public function test_pending_payments_are_listed_for_review()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(['name' => 'Jane Customer']);
        $payment = $this->pendingPayment($customer);

        $this->actingAs($admin)
            ->get('/admin/payments')
            ->assertOk()
            ->assertSee('Jane Customer')
            ->assertSee($payment->invoice->number);
    }

    public function test_admin_approves_payment_and_invoice_is_paid()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $plan = Plan::factory()->create(['device_limit' => 5]);
        $payment = $this->pendingPayment($customer, ['plan_id' => $plan->id]);

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/approve")
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('approved', $payment->status);
        $this->assertSame($admin->id, $payment->reviewed_by);
        $this->assertNotNull($payment->reviewed_at);

        $invoice = $payment->invoice->fresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        $customer->refresh();
        $this->assertSame($plan->id, $customer->plan_id);

        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.approved', 'auditable_id' => $payment->id]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'type' => PaymentApproved::class,
        ]);
    }

    public function test_admin_rejects_payment_with_a_reason()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $payment = $this->pendingPayment($customer);

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/reject", ['reason' => 'Amount does not match the invoice.'])
            ->assertRedirect();

        $payment->refresh();
        $this->assertSame('rejected', $payment->status);
        $this->assertSame('Amount does not match the invoice.', $payment->rejection_reason);

        $invoice = $payment->invoice->fresh();
        $this->assertSame('rejected', $invoice->status);
        $this->assertSame('Amount does not match the invoice.', $invoice->rejection_reason);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'type' => PaymentRejected::class,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'payment.rejected']);
    }

    public function test_rejection_requires_a_reason()
    {
        $admin = User::factory()->admin()->create();
        $payment = $this->pendingPayment(User::factory()->create());

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/reject")
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending_verification', $payment->fresh()->status);
    }

    public function test_already_reviewed_payment_cannot_be_reviewed_again()
    {
        $admin = User::factory()->admin()->create();
        $payment = $this->pendingPayment(User::factory()->create());
        $payment->update(['status' => 'approved']);

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/approve")
            ->assertStatus(422);

        $this->actingAs($admin)
            ->post("/admin/payments/{$payment->id}/reject", ['reason' => 'Nope'])
            ->assertStatus(422);
    }

    public function test_proof_file_is_only_accessible_to_admins()
    {
        Storage::fake('local');
        Storage::disk('local')->put('payment-proofs/receipt.jpg', 'fake-image-content');

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $payment = $this->pendingPayment($customer);

        $this->get("/admin/payments/{$payment->id}/proof")
            ->assertRedirect('/login');

        $this->actingAs($admin)
            ->get("/admin/payments/{$payment->id}/proof")
            ->assertOk();

        $this->actingAs($customer)
            ->get("/admin/payments/{$payment->id}/proof")
            ->assertForbidden();
    }
}
