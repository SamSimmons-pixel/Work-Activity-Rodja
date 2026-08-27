<?php

namespace App\Events;

use App\Models\ActivityComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityCommentPosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ActivityComment $comment
    ) {}

    /**
     * Broadcast on the private channel for the specific activity.
     * Only users authorized via channels.php can receive this.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("activity.{$this->comment->activity_id}"),
        ];
    }

    /**
     * Data sent to the client.
     */
    public function broadcastWith(): array
    {
        return [
            'comment_id'     => $this->comment->id,
            'activity_id'    => $this->comment->activity_id,
            'user_id'        => $this->comment->user_id,
            'commenter_name' => $this->comment->user?->full_name ?? 'Unknown',
            'commenter_role' => $this->comment->user?->role?->name ?? '',
            'comment'        => $this->comment->comment,
            'created_at'     => $this->comment->created_at->translatedFormat('d M Y, H:i'),
        ];
    }

    /**
     * Event name on the client side.
     */
    public function broadcastAs(): string
    {
        return 'ActivityCommentPosted';
    }
}
