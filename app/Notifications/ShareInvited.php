<?php

namespace App\Notifications;

use App\Models\DeviceShare;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShareInvited extends Notification
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
            ->subject('Device sharing invitation')
            ->greeting('Hello '.$notifiable->name)
            ->line($this->share->owner->name.' invited you to follow "'.$this->share->device->name.'".')
            ->line('Accepting records your consent, as required before monitoring begins.')
            ->action('Review invitation', route('shares.index'))
            ->line('You can revoke sharing at any time.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'share_invited',
            'share_id' => $this->share->id,
            'title' => 'Sharing invitation',
            'body' => $this->share->owner->name.' invited you to follow "'.$this->share->device->name.'".',
            'severity' => 'info',
            'url' => route('shares.index'),
        ];
    }
}
