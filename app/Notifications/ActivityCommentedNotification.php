<?php

namespace App\Notifications;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ActivityCommentedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Activity $activity,
        public User $commenter,
        public string $commentText
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
            'type' => 'comment',
            'title' => 'Catatan / Evaluasi Baru',
            'message' => "{$this->commenter->full_name} memberikan catatan: \"" . \Illuminate\Support\Str::limit($this->commentText, 60) . '"',
            'activity_id' => $this->activity->id,
            'sender_id' => $this->commenter->id,
            'sender_name' => $this->commenter->full_name,
            'sender_role' => $this->commenter->role?->name ?? 'Supervisor',
            'activity_date' => $this->activity->activity_date->translatedFormat('d F Y'),
            'url' => route('dashboard', ['user_id' => $this->activity->user_id]),
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
