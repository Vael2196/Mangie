import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const scheme = import.meta.env.VITE_REVERB_SCHEME
    ?? (window.location.protocol === 'https:' ? 'https' : 'http');

const forceTLS = scheme === 'https';
const port = Number(
    import.meta.env.VITE_REVERB_PORT
        ?? (forceTLS ? 443 : 8080)
);

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST
        ?? window.location.hostname,
    wsPort: port,
    wssPort: port,
    forceTLS,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
});

if (import.meta.env.DEV) {
    const connection = window.Echo.connector?.pusher?.connection;

    connection?.bind('connected', () => {
        console.info('[realtime] connected', connection.socket_id);
    });

    connection?.bind('state_change', ({ previous, current }) => {
        console.debug(`[realtime] ${previous} -> ${current}`);
    });

    connection?.bind('error', error => {
        console.error('[realtime] connection error', error);
    });
}
