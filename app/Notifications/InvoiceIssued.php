<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceIssued extends Notification
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
            ->subject('Invoice '.$this->invoice->number.' — '.$this->invoice->totalLabel())
            ->greeting('Hello '.$notifiable->name)
            ->line('An invoice has been issued for your account.')
            ->line('Amount due: '.$this->invoice->totalLabel())
            ->when($this->invoice->due_at, fn (MailMessage $mail) => $mail->line('Due: '.$this->invoice->due_at->format('M j, Y')))
            ->action('View invoice', route('billing.show', $this->invoice))
            ->line('Payment options and instructions are shown on the invoice page.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'invoice',
            'invoice_id' => $this->invoice->id,
            'title' => 'New invoice '.$this->invoice->number,
            'body' => 'Amount due: '.$this->invoice->totalLabel().'. Choose a payment method and upload your proof of payment.',
            'severity' => 'info',
            'url' => route('billing.show', $this->invoice),
        ];
    }
}
