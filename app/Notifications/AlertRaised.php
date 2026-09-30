<?php

namespace App\Notifications;

use App\Models\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertRaised extends Notification
{
    use Queueable;

    public function __construct(public readonly Alert $alert) {}

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
            ->subject('New alert: '.$this->alert->title)
            ->greeting('Hello '.$notifiable->name)
            ->line($this->alert->title)
            ->line($this->alert->body)
            ->action('Review alerts', route('alerts.index'))
            ->line('You are receiving this because alerts are enabled for your account.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'alert',
            'alert_id' => $this->alert->id,
            'title' => $this->alert->title,
            'body' => $this->alert->body,
            'severity' => $this->alert->severity,
            'url' => route('alerts.index'),
        ];
    }
}
