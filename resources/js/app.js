import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Initialize Laravel Echo with the Pusher-compatible driver,
 * pointed at the Laravel Reverb WebSocket server.
 *
 * All Livewire echo-* listeners (notifications + activity comments)
 * depend on this being initialized before Livewire boots.
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
