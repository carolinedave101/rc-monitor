<?php

namespace App\Notifications;

use App\Models\DeviceCommand;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CommandCompleted extends Notification
{
    use Queueable;

    public function __construct(public readonly DeviceCommand $command) {}

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
        $deviceName = $this->command->device?->name ?? 'device';
        $status = $this->command->status;

        return [
            'type' => 'command',
            'command_id' => $this->command->id,
            'title' => 'Remote command '.$status,
            'body' => $this->command->label().' on '.$deviceName.' was '.$status.'.',
            'severity' => $status === 'failed' ? 'warning' : 'info',
            'url' => route('devices.show', $this->command->device_id),
        ];
    }
}
