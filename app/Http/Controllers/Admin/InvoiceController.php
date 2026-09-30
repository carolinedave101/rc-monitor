<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\PaymentMethod;
use App\Models\Plan;
use App\Models\ServiceStep;
use App\Models\User;
use App\Services\InvoiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceManager $invoices) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');
        $status = in_array($status, Invoice::STATUSES, true) ? $status : null;

        $invoices = Invoice::query()
            ->with(['user', 'plan', 'serviceStep'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.invoices.index', compact('invoices', 'status'));
    }

    public function create(Request $request): View
    {
        $users = User::query()->orderBy('name')->get();
        $plans = Plan::query()->active()->ordered()->get();
        $steps = ServiceStep::query()->with('user')->orderBy('user_id')->orderBy('position')->get();
        $methods = PaymentMethod::query()->enabled()->ordered()->get();

        return view('admin.invoices.create', compact('users', 'plans', 'steps', 'methods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'nullable|exists:plans,id',
            'service_step_id' => 'nullable|exists:service_steps,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.5|max:100000',
            'due_at' => 'nullable|date',
            'notes' => 'nullable|string|max:2000',
            'methods' => 'required|array|min:1',
            'methods.*' => 'exists:payment_methods,id',
        ]);

        if (! empty($data['service_step_id'])) {
            $step = ServiceStep::find($data['service_step_id']);

            if ($step->user_id !== (int) $data['user_id']) {
                return back()
                    ->withErrors(['service_step_id' => 'The selected step does not belong to this customer.'])
                    ->withInput();
            }
        }

        $amountCents = (int) round($data['amount'] * 100);

        $invoice = Invoice::create([
            'user_id' => $data['user_id'],
            'plan_id' => $data['plan_id'] ?? null,
            'service_step_id' => $data['service_step_id'] ?? null,
            'number' => Invoice::generateNumber(),
            'status' => 'draft',
            'subtotal_cents' => $amountCents,
            'total_cents' => $amountCents,
            'due_at' => $data['due_at'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        $invoice->items()->create([
            'description' => $data['description'],
            'quantity' => 1,
            'unit_price_cents' => $amountCents,
        ]);

        $invoice->paymentMethods()->sync($data['methods']);

        if ($invoice->serviceStep && ! $invoice->serviceStep->isCompleted()) {
            $invoice->serviceStep->update(['status' => 'awaiting_payment']);

            AuditLog::record('journey.step.awaiting_payment', $invoice->serviceStep, [
                'invoice' => $invoice->number,
            ]);
        }

        AuditLog::record('invoice.created', $invoice, [
            'number' => $invoice->number,
            'total_cents' => $amountCents,
            'user_id' => $invoice->user_id,
        ]);

        return redirect()->route('admin.invoices.show', $invoice)
            ->with('status', 'Invoice '.$invoice->number.' created as a draft. Send it when you are ready.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['user', 'plan', 'serviceStep', 'items', 'paymentMethods', 'payments.reviewedBy']);

        $methods = PaymentMethod::query()->enabled()->ordered()->get();

        return view('admin.invoices.show', compact('invoice', 'methods'));
    }

    public function send(Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->isPaid() || $invoice->status === 'void', 422, 'This invoice cannot be sent.');

        $this->invoices->send($invoice);

        return back()->with('status', 'Invoice '.$invoice->number.' sent to '.$invoice->user->name.'.');
    }

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        abort_if($invoice->isPaid() || $invoice->status === 'void', 422, 'This invoice cannot be marked paid.');

        $invoice->payments()->create([
            'user_id' => $invoice->user_id,
            'amount_cents' => $invoice->total_cents,
            'status' => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'reference' => 'Marked paid manually',
        ]);

        $this->invoices->markPaid($invoice);

        AuditLog::record('invoice.marked_paid', $invoice, ['number' => $invoice->number]);

        return back()->with('status', 'Invoice '.$invoice->number.' marked as paid.');
    }

    public function void(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => 'void']);

        $step = $invoice->serviceStep;

        if ($step && $step->status === 'awaiting_payment') {
            $step->update(['status' => 'in_progress']);

            AuditLog::record('journey.step.payment_reverted', $step, ['invoice' => $invoice->number]);
        }

        AuditLog::record('invoice.voided', $invoice, ['number' => $invoice->number]);

        return back()->with('status', 'Invoice '.$invoice->number.' voided.');
    }

    public function updateMethods(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'methods' => 'required|array|min:1',
            'methods.*' => 'exists:payment_methods,id',
        ]);

        $invoice->paymentMethods()->sync($data['methods']);

        AuditLog::record('invoice.methods_updated', $invoice, ['methods' => $data['methods']]);

        return back()->with('status', 'Payment methods updated for '.$invoice->number.'.');
    }
}
