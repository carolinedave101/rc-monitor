<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentRejected extends Notification
{
    use Queueable;

    public function __construct(public readonly Payment $payment) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $invoice = $this->payment->invoice;

        return (new MailMessage)
            ->subject('Payment needs attention — invoice '.$invoice->number)
            ->greeting('Hello '.$notifiable->name)
            ->line('We could not verify your payment for invoice '.$invoice->number.'.')
            ->line('Reason: '.$this->payment->rejection_reason)
            ->line('You can upload a new proof of payment on the invoice page.')
            ->action('View invoice', route('billing.show', $invoice));
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
            'title' => 'Payment could not be verified',
            'body' => 'Invoice '.$invoice->number.': '.$this->payment->rejection_reason,
            'severity' => 'warning',
            'url' => route('billing.show', $invoice),
        ];
    }
}
