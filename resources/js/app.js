import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/**
 * Initialize Laravel Echo with Laravel Reverb
 */
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

window.readAndRedirect = function (notifId, url) {
    const component = window.Livewire?.all()?.find(c => c.name === 'notifications.notification-menu');

    if (component) {
        component.call('markAsRead', notifId, url);
    } else if (url) {
        window.location.href = url;
    }
};


/**
 * Real-time DOM append for Activity Comments (Vanilla JS)
 */
function appendActivityComment(data) {
    const container = document.getElementById('activity-comments-container-' + data.activity_id);
    const list = document.getElementById('activity-comments-list-' + data.activity_id);
    if (!list) return;

    // Show container if it was hidden
    if (container && container.classList.contains('hidden')) {
        container.classList.remove('hidden');
    }

    // Check if element already exists (prevent duplicate)
    if (data.comment_id && document.getElementById('comment-item-' + data.comment_id)) {
        return;
    }

    // Create new comment item element
    const item = document.createElement('div');
    if (data.comment_id) {
        item.id = 'comment-item-' + data.comment_id;
    }
    item.className = 'bg-indigo-50/60 rounded-xl p-3 border border-indigo-100 text-xs text-slate-800 space-y-1 transition-all duration-300';
    item.innerHTML = `
        <div class="flex items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
                <span class="font-bold text-indigo-900">${data.commenter_name || 'User'}</span>
                <span class="text-2xs px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 font-semibold">${data.commenter_role || 'Supervisor'}</span>
            </div>
            <span class="text-2xs text-slate-400">Baru saja</span>
        </div>
        <p class="text-slate-700 leading-relaxed">${data.comment || ''}</p>
    `;

    list.appendChild(item);
}

/**
 * Real-time DOM append for Notifications & Badge Increment (Vanilla JS)
 */
function appendNotificationItem(notif) {
    const list = document.getElementById('notification-items-list');
    const badge = document.getElementById('notification-badge-count');
    const headerBadge = document.getElementById('notification-header-badge');
    const headerCount = document.getElementById('notification-header-count');
    const filterCount = document.getElementById('notification-filter-count');
    const emptyState = document.getElementById('notification-empty-state');

    // Remove empty state if present
    if (emptyState) {
        emptyState.remove();
    }

    // Increment notification badge count
    if (badge) {
        let currentCount = parseInt(badge.innerText) || 0;
        currentCount++;
        badge.innerText = currentCount > 99 ? '99+' : currentCount;
        badge.classList.remove('hidden');
    }
    if (headerBadge && headerCount) {
        let count = parseInt(headerCount.innerText) || 0;
        count++;
        headerCount.innerText = count;
        headerBadge.classList.remove('hidden');
    }
    if (filterCount) {
        let count = parseInt(filterCount.innerText) || 0;
        count++;
        filterCount.innerText = count;
    }

    if (!list) return;

    const notifId = notif.id || ('temp-' + Date.now());
    if (document.getElementById('notif-item-' + notifId)) return;

    // Type icon HTML
    let iconHtml = `
        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shadow-2xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
    `;
    if (notif.type === 'comment') {
        iconHtml = `
            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
            </div>
        `;
    } else if (notif.type === 'verified') {
        iconHtml = `
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
        `;
    }

    const item = document.createElement('div');
    item.id = 'notif-item-' + notifId;
    item.className = 'group relative flex items-start gap-3 p-3.5 transition cursor-pointer bg-indigo-50/50 hover:bg-indigo-50/80 border-l-4 border-indigo-600';
    item.innerHTML = `
        <div class="shrink-0 mt-0.5">${iconHtml}</div>
        <div class="flex-1 min-w-0" onclick="window.readAndRedirect('${notif.id}', '${notif.url || ''}')">
            <div class="flex items-center justify-between gap-1">
                <h4 class="text-xs font-black text-slate-900 truncate">${notif.title || 'Pemberitahuan Baru'}</h4>
                <span class="text-3xs text-slate-400 shrink-0">Baru saja</span>
            </div>
            <p class="text-2xs text-slate-800 font-medium line-clamp-2 mt-0.5 leading-relaxed">${notif.message || ''}</p>
            ${notif.sender_name ? `
                <div class="flex items-center gap-1.5 mt-1 text-3xs text-slate-400">
                    <span class="font-bold text-slate-600">${notif.sender_name}</span>
                    ${notif.sender_role ? `<span>&bull;</span><span>${notif.sender_role}</span>` : ''}
                </div>
            ` : ''}
        </div>
        <div class="flex flex-col items-end justify-between self-stretch shrink-0">
            <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 shadow-xs mt-1 shrink-0" title="Belum dibaca"></span>
        </div>
    `;

    list.prepend(item);
}

// Track active activity channels to avoid duplicate subscriptions
const subscribedActivityChannels = new Set();

/**
 * Setup Echo subscriptions for User Notifications and visible Activities
 */
function initEchoListeners() {
    if (!window.Echo) return;

    // 1. Listen to Private User Notification Channel
    const userIdMeta = document.querySelector('meta[name="auth-user-id"]');
    const authUserId = userIdMeta ? userIdMeta.content : null;

    if (authUserId && !window._userChannelSubscribed) {
        window._userChannelSubscribed = true;
        window.Echo.private(`App.Models.User.${authUserId}`)
            .notification((notification) => {
                appendNotificationItem(notification);
            });
    }

    // 2. Listen to all Activity Cards currently on screen
    const activityCards = document.querySelectorAll('[data-activity-card]');
    activityCards.forEach((card) => {
        const activityId = card.getAttribute('data-activity-card');
        if (activityId && !subscribedActivityChannels.has(activityId)) {
            subscribedActivityChannels.add(activityId);
            window.Echo.private(`activity.${activityId}`)
                .listen('.ActivityCommentPosted', (data) => {
                    appendActivityComment(data);
                });
        }
    });
}

// Initialize on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEchoListeners);
} else {
    initEchoListeners();
}

// Re-scan when Livewire updates or navigates
document.addEventListener('livewire:navigated', initEchoListeners);
document.addEventListener('livewire:initialized', initEchoListeners);
