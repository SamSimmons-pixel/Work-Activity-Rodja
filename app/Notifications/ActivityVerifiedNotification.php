<?php

namespace App\Notifications;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ActivityVerifiedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Activity $activity,
        public User $verifier,
        public string $newStatus
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
        $statusLabel = $this->newStatus === 'Verified' ? 'Terverifikasi' : 'Ditinjau';

        return [
            'type' => 'verified',
            'title' => "Aktivitas Ditandai {$statusLabel}",
            'message' => "Aktivitas tanggal {$this->activity->activity_date->translatedFormat('d F Y')} telah ditandai sebagai {$statusLabel} oleh {$this->verifier->full_name}.",
            'activity_id' => $this->activity->id,
            'sender_id' => $this->verifier->id,
            'sender_name' => $this->verifier->full_name,
            'status' => $this->newStatus,
            'activity_date' => $this->activity->activity_date->translatedFormat('d F Y'),
            'url' => route('dashboard', [
                'user_id' => $this->activity->user_id,
                'year' => (int) $this->activity->activity_date->format('Y'),
                'month' => (int) $this->activity->activity_date->format('n'),
            ]),
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
