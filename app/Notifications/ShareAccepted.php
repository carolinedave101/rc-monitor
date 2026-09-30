<?php

namespace App\Notifications;

use App\Models\DeviceShare;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShareAccepted extends Notification
{
    use Queueable;

    public function __construct(public readonly DeviceShare $share) {}

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
            ->subject('Invitation accepted: '.$this->share->device->name)
            ->greeting('Hello '.$notifiable->name)
            ->line(($this->share->viewer?->name ?? $this->share->email).' accepted your invitation to follow "'.$this->share->device->name.'".')
            ->action('Manage sharing', route('shares.index'))
            ->line('They can now see this device from their account and either side can revoke access at any time.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'share_accepted',
            'share_id' => $this->share->id,
            'title' => 'Sharing invitation accepted',
            'body' => ($this->share->viewer?->name ?? $this->share->email).' accepted access to "'.$this->share->device->name.'".',
            'severity' => 'info',
            'url' => route('shares.index'),
        ];
    }
}
