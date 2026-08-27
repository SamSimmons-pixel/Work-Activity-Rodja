import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Initialize Laravel Echo with the Pusher-compatible driver,
 * pointed at the Laravel Reverb WebSocket server.
 */
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8081,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

document.addEventListener('alpine:init', () => {
    // 1. Real-time Notification Menu (maps notifications directly into DOM without page reload)
    window.Alpine.data('notificationMenu', (config) => ({
        open: false,
        unreadCount: config.initialUnreadCount || 0,
        notifications: config.initialNotifications || [],
        filter: 'all',
        userId: config.userId,

        init() {
            if (window.Echo && this.userId) {
                window.Echo.private(`App.Models.User.${this.userId}`)
                    .notification((notif) => {
                        this.unreadCount++;
                        // Prepend new notification to reactive array in real-time
                        this.notifications.unshift({
                            id: notif.id || ('temp-' + Date.now()),
                            read_at: null,
                            title: notif.title || 'Pemberitahuan Baru',
                            message: notif.message || '',
                            sender_name: notif.sender_name || null,
                            sender_role: notif.sender_role || null,
                            type: notif.type || 'general',
                            url: notif.url || null,
                            time_ago: 'Baru saja'
                        });
                    });
            }
        },

        get filteredNotifications() {
            if (this.filter === 'unread') {
                return this.notifications.filter(n => !n.read_at);
            }
            return this.notifications;
        },

        markRead(notifId, url) {
            const notif = this.notifications.find(n => n.id === notifId);
            if (notif && !notif.read_at) {
                notif.read_at = new Date().toISOString();
                if (this.unreadCount > 0) this.unreadCount--;
            }
            if (this.$wire) {
                this.$wire.markAsRead(notifId, url);
            } else if (url) {
                window.location.href = url;
            }
        },

        markAllRead() {
            this.notifications.forEach(n => n.read_at = new Date().toISOString());
            this.unreadCount = 0;
            if (this.$wire) {
                this.$wire.markAllAsRead();
            }
        },

        deleteNotif(notifId) {
            this.notifications = this.notifications.filter(n => n.id !== notifId);
            if (this.$wire) {
                this.$wire.deleteNotification(notifId);
            }
        }
    }));

    // 2. Real-time Activity Comments (maps new messages directly into activity card in real-time)
    window.Alpine.data('activityComments', (config) => ({
        activityId: config.activityId,
        comments: config.initialComments || [],

        init() {
            if (window.Echo && this.activityId) {
                window.Echo.private(`activity.${this.activityId}`)
                    .listen('.ActivityCommentPosted', (e) => {
                        // Prevent duplicate if already received
                        if (!this.comments.some(c => c.id === e.comment_id)) {
                            this.comments.push({
                                id: e.comment_id,
                                user_name: e.commenter_name,
                                user_role: e.commenter_role || 'Supervisor',
                                comment: e.comment,
                                time_ago: e.created_at || 'Baru saja'
                            });
                        }
                    });
            }
        }
    }));
});

