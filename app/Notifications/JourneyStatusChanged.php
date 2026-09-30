<?php

namespace App\Notifications;

use App\Models\ServiceStep;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JourneyStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public readonly ServiceStep $step,
        public readonly string $change,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->greeting('Hello '.$notifiable->name);

        if ($this->change === 'paused') {
            $message->subject('Your service plan is paused')
                ->line("The step \"{$this->step->title}\" was paused.")
                ->line($this->step->paused_reason ? 'Reason: '.$this->step->paused_reason : '')
                ->line('We will continue as soon as the pause is lifted.');
        } else {
            $message->subject('Your service plan is active again')
                ->line("The step \"{$this->step->title}\" was resumed.");
        }

        return $message
            ->action('View your plan', route('journey.index'))
            ->line('You are receiving this because of activity on your ROYALTRICO account.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $paused = $this->change === 'paused';

        return [
            'type' => 'journey',
            'step_id' => $this->step->id,
            'change' => $this->change,
            'title' => $paused ? 'Service plan paused' : 'Service plan resumed',
            'body' => $paused
                ? "Step \"{$this->step->title}\" paused".($this->step->paused_reason ? ': '.$this->step->paused_reason : '.')
                : "Step \"{$this->step->title}\" resumed.",
            'severity' => 'warning',
            'url' => route('journey.index'),
        ];
    }
}
