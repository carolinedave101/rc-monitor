<?php

namespace Tests\Feature\Admin;

use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\ServiceStep;
use App\Models\User;
use App\Notifications\InvoiceIssued;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function payload(User $customer, array $overrides = []): array
    {
        return array_merge([
            'user_id' => $customer->id,
            'description' => 'Standard plan — up to 5 devices',
            'amount' => 49.00,
            'methods' => PaymentMethod::factory()->count(2)->create()->pluck('id')->all(),
        ], $overrides);
    }

    public function test_non_admin_cannot_access_invoices()
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/invoices')->assertForbidden();
        $this->actingAs($user)->get('/admin/invoices/create')->assertForbidden();
    }

    public function test_admin_creates_a_draft_invoice_with_methods()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $methods = PaymentMethod::factory()->count(2)->create();

        $this->actingAs($admin)
            ->post('/admin/invoices', [
                'user_id' => $customer->id,
                'description' => 'Standard plan — up to 5 devices',
                'amount' => 49.00,
                'methods' => $methods->pluck('id')->all(),
            ])
            ->assertRedirect();

        $invoice = Invoice::first();
        $this->assertSame('draft', $invoice->status);
        $this->assertSame(4900, $invoice->total_cents);
        $this->assertStringStartsWith('INV-', $invoice->number);
        $this->assertDatabaseHas('invoice_items', ['invoice_id' => $invoice->id, 'description' => 'Standard plan — up to 5 devices']);
        $this->assertSame(2, $invoice->paymentMethods()->count());
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.created', 'auditable_id' => $invoice->id]);
    }

    public function test_step_must_belong_to_the_invoiced_customer()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $otherStep = ServiceStep::factory()->for(User::factory())->create();

        $this->actingAs($admin)
            ->post('/admin/invoices', $this->payload($customer, ['service_step_id' => $otherStep->id]))
            ->assertSessionHasErrors('service_step_id');

        $this->assertSame(0, Invoice::count());
    }

    public function test_admin_sends_invoice_and_customer_is_notified()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $invoice = Invoice::factory()->for($customer)->draft()->create();

        $this->actingAs($admin)
            ->post("/admin/invoices/{$invoice->id}/send")
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('sent', $invoice->status);
        $this->assertNotNull($invoice->issued_at);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'type' => InvoiceIssued::class,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.sent', 'auditable_id' => $invoice->id]);
    }

    public function test_admin_marking_paid_applies_plan_entitlement()
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $plan = Plan::factory()->create(['device_limit' => 5]);
        $invoice = Invoice::factory()->for($customer)->create(['plan_id' => $plan->id]);

        $this->actingAs($admin)
            ->post("/admin/invoices/{$invoice->id}/mark-paid")
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        $customer->refresh();
        $this->assertSame($plan->id, $customer->plan_id);
        $this->assertNotNull($customer->plan_activated_at);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'status' => 'approved',
            'reviewed_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.marked_paid']);
    }

    public function test_admin_can_void_an_invoice()
    {
        $admin = User::factory()->admin()->create();
        $invoice = Invoice::factory()->for(User::factory())->create();

        $this->actingAs($admin)
            ->post("/admin/invoices/{$invoice->id}/void")
            ->assertRedirect();

        $this->assertSame('void', $invoice->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.voided']);
    }

    public function test_admin_can_update_invoice_payment_methods()
    {
        $admin = User::factory()->admin()->create();
        $invoice = Invoice::factory()->for(User::factory())->create();
        $old = PaymentMethod::factory()->create();
        $new = PaymentMethod::factory()->create();
        $invoice->paymentMethods()->attach($old->id);

        $this->actingAs($admin)
            ->post("/admin/invoices/{$invoice->id}/methods", ['methods' => [$new->id]])
            ->assertRedirect();

        $this->assertSame(1, $invoice->paymentMethods()->count());
        $this->assertTrue($invoice->paymentMethods()->whereKey($new->id)->exists());
        $this->assertDatabaseHas('audit_logs', ['action' => 'invoice.methods_updated']);
    }
}
