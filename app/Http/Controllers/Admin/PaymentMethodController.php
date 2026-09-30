<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentMethodController extends Controller
{
    public function index(): View
    {
        $methods = PaymentMethod::query()->ordered()->get();

        return view('admin.payment-methods.index', compact('methods'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(PaymentMethod::TYPES)],
            'details' => 'nullable|string|max:2000',
        ]);

        $method = PaymentMethod::create([
            'label' => $data['label'],
            'type' => $data['type'],
            'details' => $data['details'] ?? null,
            'enabled' => true,
            'sort' => (int) PaymentMethod::query()->max('sort') + 10,
        ]);

        AuditLog::record('payment_method.created', $method, ['label' => $method->label]);

        return back()->with('status', "Payment method \"{$method->label}\" added.");
    }

    public function update(Request $request, PaymentMethod $paymentMethod): RedirectResponse
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'type' => ['required', Rule::in(PaymentMethod::TYPES)],
            'details' => 'nullable|string|max:2000',
        ]);

        $paymentMethod->update([
            'label' => $data['label'],
            'type' => $data['type'],
            'details' => $data['details'] ?? null,
            'enabled' => $request->boolean('enabled'),
        ]);

        AuditLog::record('payment_method.updated', $paymentMethod, [
            'label' => $paymentMethod->label,
            'enabled' => $paymentMethod->enabled,
        ]);

        return back()->with('status', "Payment method \"{$paymentMethod->label}\" updated.");
    }
}
