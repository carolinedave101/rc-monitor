<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentApproved extends Notification
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment approved — invoice '.$this->invoice->number)
            ->greeting('Hello '.$notifiable->name)
            ->line('Your payment for invoice '.$this->invoice->number.' ('.$this->invoice->totalLabel().') has been approved.')
            ->line('Thank you — your account has been updated.')
            ->action('View billing', route('billing.show', $this->invoice));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment',
            'invoice_id' => $this->invoice->id,
            'title' => 'Payment approved',
            'body' => 'Invoice '.$this->invoice->number.' ('.$this->invoice->totalLabel().') was approved.',
            'severity' => 'info',
            'url' => route('billing.show', $this->invoice),
        ];
    }
}
