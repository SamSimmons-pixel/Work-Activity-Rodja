<?php

namespace App\Notifications;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ActivitySubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Activity $activity,
        public User $employee
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activity',
            'title' => 'Aktivitas Kerja Baru',
            'message' => "{$this->employee->full_name} telah mencatat aktivitas pekerjaan untuk tanggal {$this->activity->activity_date->translatedFormat('d F Y')}.",
            'activity_id' => $this->activity->id,
            'sender_id' => $this->employee->id,
            'sender_name' => $this->employee->full_name,
            'category' => $this->activity->category ?? 'Umum',
            'activity_date' => $this->activity->activity_date->translatedFormat('d F Y'),
            'url' => route('dashboard', ['user_id' => $this->employee->id]),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
