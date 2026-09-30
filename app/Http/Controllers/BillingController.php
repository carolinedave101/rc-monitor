<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\PaymentSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $plans = Plan::query()->active()->ordered()->get();
        $deviceCount = $user->devices()->count();
        $invoices = $user->invoices()->with(['plan', 'serviceStep'])->latest()->get();

        return view('billing.index', compact('user', 'plans', 'deviceCount', 'invoices'));
    }

    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);

        $invoice->load(['items', 'paymentMethods', 'payments.paymentMethod', 'plan', 'serviceStep']);

        return view('billing.show', compact('invoice'));
    }

    public function submitPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);
        abort_unless(in_array($invoice->status, ['sent', 'rejected'], true), 422, 'This invoice is not awaiting payment.');

        $data = $request->validate([
            'payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('enabled', true)],
            'reference' => 'nullable|string|max:255',
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        abort_unless(
            $invoice->paymentMethods()->whereKey($data['payment_method_id'])->exists(),
            422,
            'That payment method is not available on this invoice.',
        );

        $file = $request->file('proof');
        $path = $file->store('payment-proofs', 'local');

        $payment = $invoice->payments()->create([
            'user_id' => $request->user()->id,
            'payment_method_id' => $data['payment_method_id'],
            'amount_cents' => $invoice->total_cents,
            'reference' => $data['reference'] ?? null,
            'proof_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'pending_verification',
        ]);

        $invoice->update([
            'status' => 'awaiting_verification',
            'rejection_reason' => null,
        ]);

        $step = $invoice->serviceStep;

        if ($step && $step->status === 'awaiting_payment') {
            $step->update(['status' => 'awaiting_verification']);

            AuditLog::record('journey.step.awaiting_verification', $step, ['invoice' => $invoice->number]);
        }

        AuditLog::record('payment.submitted', $payment, [
            'invoice' => $invoice->number,
            'amount_cents' => $payment->amount_cents,
        ]);

        User::query()->where('is_admin', true)->get()->each(
            fn (User $admin) => $admin->notify(new PaymentSubmitted($payment->load('invoice', 'user'))),
        );

        return back()->with('status', 'Proof of payment uploaded. We will verify it and update your invoice shortly.');
    }
}
