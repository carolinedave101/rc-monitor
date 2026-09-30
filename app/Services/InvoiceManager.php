<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\InvoiceIssued;
use App\Notifications\PaymentApproved;
use App\Notifications\PaymentRejected;

class InvoiceManager
{
    public function __construct(private readonly JourneyManager $journeys) {}

    public function send(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'sent',
            'issued_at' => now(),
            'rejection_reason' => null,
        ]);

        $invoice->user->notify(new InvoiceIssued($invoice));

        AuditLog::record('invoice.sent', $invoice, [
            'number' => $invoice->number,
            'total_cents' => $invoice->total_cents,
        ]);
    }

    public function markPaid(Invoice $invoice): void
    {
        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
            'rejection_reason' => null,
        ]);

        if ($invoice->plan_id) {
            $invoice->user->update([
                'plan_id' => $invoice->plan_id,
                'plan_activated_at' => now(),
            ]);
        }

        $this->applyStepEntitlement($invoice);

        $invoice->user->notify(new PaymentApproved($invoice));
    }

    protected function applyStepEntitlement(Invoice $invoice): void
    {
        $step = $invoice->serviceStep;

        if (! $step || $step->isCompleted() || $step->isPaused()) {
            return;
        }

        $this->journeys->advance($step);
    }

    public function approvePayment(Payment $payment, User $admin): void
    {
        $payment->update([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $this->markPaid($payment->invoice);

        AuditLog::record('payment.approved', $payment, [
            'invoice' => $payment->invoice->number,
            'amount_cents' => $payment->amount_cents,
        ]);
    }

    public function rejectPayment(Payment $payment, User $admin, string $reason): void
    {
        $payment->update([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $payment->invoice->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        $step = $payment->invoice->serviceStep;

        if ($step && $step->status === 'awaiting_verification') {
            $step->update(['status' => 'awaiting_payment']);
        }

        $payment->user->notify(new PaymentRejected($payment));

        AuditLog::record('payment.rejected', $payment, [
            'invoice' => $payment->invoice->number,
            'reason' => $reason,
        ]);
    }
}
