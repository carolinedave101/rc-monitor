<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSubmitted extends Notification
{
    use Queueable;

    public function __construct(public readonly Payment $payment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $invoice = $this->payment->invoice;

        return [
            'type' => 'payment',
            'invoice_id' => $invoice->id,
            'title' => 'Payment proof submitted',
            'body' => ($this->payment->user?->name ?? 'A customer').' submitted proof for invoice '.$invoice->number.' ('.$this->payment->amountLabel().').',
            'severity' => 'info',
            'url' => route('admin.payments.index'),
        ];
    }
}
