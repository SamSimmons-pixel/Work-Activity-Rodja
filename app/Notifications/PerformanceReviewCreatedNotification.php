<?php

namespace App\Notifications;

use App\Models\PerformanceReview;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PerformanceReviewCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PerformanceReview $review,
        public User $reviewer
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
            'type' => 'review',
            'title' => 'Evaluasi Kinerja Berkala Dipublikasikan',
            'message' => "{$this->reviewer->full_name} telah mempublikasikan review kinerja Anda untuk periode {$this->review->period_label}.",
            'review_id' => $this->review->id,
            'sender_id' => $this->reviewer->id,
            'sender_name' => $this->reviewer->full_name,
            'rating' => $this->review->rating,
            'period_label' => $this->review->period_label,
            'url' => route('reviews.index'),
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
