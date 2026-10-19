import { jsonRequest } from './http';

let lastReported = 0;
let reporting = false;
async function reportActivity() {
    if (document.hidden || reporting || Date.now() - lastReported < 60000) return;
    reporting = true;
    try {
        const response = await fetch('/activity', { method: 'POST', credentials: 'same-origin', headers: {
            Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        } });
        if (response.ok) lastReported = Date.now();
    } catch { /* Activity reporting must never interrupt editing. */ }
    finally { reporting = false; }
}

function applyPresence(element, presence, name) {
    element.dataset.presenceStatus = presence.status;
    element.classList.toggle('presence-offline', presence.status === 'offline');
    element.title = `${name} · ${presence.label}`;
}

async function refreshPresence() {
    if (document.hidden) return;
    try {
        const self = await jsonRequest('/presence/me');
        const userId = document.querySelector('meta[name="current-user-id"]')?.content;
        document.querySelectorAll('[data-self-presence]').forEach(node => { node.textContent = self.label; node.dataset.presenceStatus = self.status; });
        document.querySelectorAll(`[data-user-avatar-id="${Number(userId)}"][data-presence-status]`).forEach(node => {
            applyPresence(node, self, node.dataset.userAvatarName);
        });
        for (const board of document.querySelectorAll('[data-presence-board]')) {
            try {
                const data = await jsonRequest(`/boards/${board.dataset.presenceBoard}/presence`);
                data.users.forEach(user => board.querySelectorAll(`[data-user-avatar-id="${Number(user.id)}"]`).forEach(node => {
                    applyPresence(node, user, node.dataset.userAvatarName);
                }));
            } catch (error) {
                if ([403, 404].includes(error.status)) {
                    board.querySelectorAll('[data-presence-status]').forEach(node => {
                        delete node.dataset.presenceStatus;
                        node.classList.remove('presence-offline');
                        node.title = node.dataset.userAvatarName;
                    });
                    delete board.dataset.presenceBoard;
                }
            }
        }
    } catch { /* Permissions are checked again on every poll. */ }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!Number(document.querySelector('meta[name="current-user-id"]')?.content)) return;
    ['pointerdown', 'keydown', 'input', 'scroll'].forEach(event => document.addEventListener(event, reportActivity, { passive: true, capture: true }));
    window.setInterval(refreshPresence, 60000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) void refreshPresence(); });
});
