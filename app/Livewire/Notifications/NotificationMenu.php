<?php

namespace App\Livewire\Notifications;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationMenu extends Component
{
    public bool $isOpen = false;
    public string $filter = 'all'; // 'all' | 'unread'
    public ?int $userId = null;

    public function mount(): void
    {
        $this->userId = Auth::id();
    }

    public function toggleMenu(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function closeMenu(): void
    {
        $this->isOpen = false;
    }

    public function setFilter(string $filter): void
    {
        $this->filter = $filter;
    }

    public function markAsRead(string $notificationId, ?string $redirectUrl = null)
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $notification = $user->notifications()->where('id', $notificationId)->first();
        if ($notification && is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        if ($redirectUrl) {
            $this->isOpen = false;
            return redirect($redirectUrl);
        }
    }

    public function markAllAsRead(): void
    {
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
    }

    public function deleteNotification(string $notificationId): void
    {
        $user = Auth::user();
        if ($user) {
            $user->notifications()->where('id', $notificationId)->delete();
        }
    }

    public function getListeners(): array
    {
        return [
            'notification-received' => '$refresh',
        ];
    }

    public function render()
    {
        $user = Auth::user();
        $unreadCount = $user ? $user->unreadNotifications()->count() : 0;

        $query = $user ? $user->notifications() : null;

        if ($query && $this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query ? $query->limit(20)->get() : collect();

        return view('livewire.notifications.notification-menu', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'user' => $user,
        ]);
    }
}
