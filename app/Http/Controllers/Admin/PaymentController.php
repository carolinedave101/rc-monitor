<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\InvoiceManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    public function __construct(private readonly InvoiceManager $invoices) {}

    public function index(): View
    {
        $pending = Payment::query()
            ->where('status', 'pending_verification')
            ->with(['invoice', 'user', 'paymentMethod'])
            ->oldest()
            ->get();

        $reviewed = Payment::query()
            ->whereIn('status', ['approved', 'rejected'])
            ->with(['invoice', 'user', 'reviewedBy'])
            ->latest('reviewed_at')
            ->limit(15)
            ->get();

        return view('admin.payments.index', compact('pending', 'reviewed'));
    }

    public function proof(Payment $payment): StreamedResponse
    {
        abort_unless($payment->proof_path, 404);

        return Storage::disk('local')->response($payment->proof_path, $payment->original_filename);
    }

    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === 'pending_verification', 422, 'This payment was already reviewed.');

        $this->invoices->approvePayment($payment, $request->user());

        return back()->with('status', 'Payment approved. Invoice '.$payment->invoice->number.' is now paid.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($payment->status === 'pending_verification', 422, 'This payment was already reviewed.');

        $reason = $request->validate([
            'reason' => 'required|string|max:1000',
        ])['reason'];

        $this->invoices->rejectPayment($payment, $request->user(), $reason);

        return back()->with('status', 'Payment rejected. The customer can see the reason and upload a new proof.');
    }
}
