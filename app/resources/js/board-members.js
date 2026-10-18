import { jsonRequest } from './http';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-board-members]');
    if (!root) return;
    const base = `/boards/${root.dataset.boardId}`;
    const status = root.querySelector('[data-members-status]');
    const query = root.querySelector('[data-invite-query]');
    const suggestions = root.querySelector('[data-invite-suggestions]');
    let selectedUser = null;
    let timer;
    let searchVersion = 0;
    const message = text => { status.textContent = text; status.classList.remove('hidden'); };
    const closeSuggestions = () => {
        suggestions?.classList.add('hidden');
        query?.setAttribute('aria-expanded', 'false');
    };

    query?.addEventListener('input', () => {
        selectedUser = null;
        clearTimeout(timer);
        const version = ++searchVersion;
        const value = query.value.trim();
        closeSuggestions();
        if (value.length < 2) return;
        timer = setTimeout(async () => {
            try {
                const data = await jsonRequest(`${base}/member-suggestions?query=${encodeURIComponent(value)}`);
                if (version !== searchVersion) return;
                suggestions.replaceChildren();
                for (const user of data.users) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'block w-full rounded-lg px-3 py-2 text-left text-sm hover:bg-indigo-50 dark:hover:bg-indigo-950';
                    button.textContent = `${user.name}${user.member ? ' · already joined' : ''}`;
                    button.disabled = user.member;
                    button.addEventListener('click', () => {
                        selectedUser = user.id;
                        query.value = user.name;
                        closeSuggestions();
                    });
                    suggestions.append(button);
                }
                if (data.users.length) {
                    suggestions.classList.remove('hidden');
                    query.setAttribute('aria-expanded', 'true');
                }
            } catch (error) { message(error.message); }
        }, 250);
    });
    document.addEventListener('click', event => {
        if (event.target !== query && !suggestions?.contains(event.target)) closeSuggestions();
    });
    query?.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeSuggestions();
    });
    root.querySelector('[data-board-invite]')?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = event.target.querySelector('button[type="submit"], button:not([type])');
        if (button?.disabled) return;
        if (button) button.disabled = true;
        try {
            const body = { role: root.querySelector('[data-invite-role]').value };
            if (selectedUser) body.user_id = selectedUser;
            else body.email = query.value.trim();
            const data = await jsonRequest(`${base}/add-user`, { method: 'POST', body });
            message(data.message);
            query.value = ''; selectedUser = null; closeSuggestions();
            // Keep feedback visible and add the invitation to the pending list.
            const note = document.createElement('p');
            note.className = 'mt-3 text-sm text-gray-500';
            note.textContent = 'Invitation sent. Reload this page to manage the new pending invitation.';
            root.querySelector('[data-pending-invitations]')?.prepend(note);
        } catch (error) { message(error.message); }
        finally { if (button) button.disabled = false; }
    });
    root.addEventListener('click', async event => {
        const button = event.target.closest('button');
        if (!button || button.disabled) return;
        const row = button.closest('[data-member-row]');
        try {
            if (button.matches('[data-create-share-link]')) {
                button.disabled = true;
                const data = await jsonRequest(`${base}/share-links`, {
                    method: 'POST', body: { role: root.querySelector('[data-invite-role]').value },
                });
                root.querySelector('[data-share-url]').value = data.url;
                root.querySelector('[data-share-result]').classList.remove('hidden');
                root.querySelector('[data-share-result]').classList.add('flex');
                message('Share link created. It expires in 7 days; keep it private.');
            } else if (button.matches('[data-copy-share-link]')) {
                const input = root.querySelector('[data-share-url]');
                try { await navigator.clipboard.writeText(input.value); message('Link copied.'); }
                catch { input.select(); message('Select and copy the link above.'); }
            } else if (button.matches('[data-save-member-role]')) {
                await jsonRequest(`${base}/members/${row.dataset.userId}`, {
                    method: 'PATCH', body: { role: row.querySelector('[data-member-role]').value },
                });
                window.location.reload();
            } else if (button.matches('[data-remove-member]')) {
                if (!confirm('Remove this participant? They will need a new invitation to rejoin.')) return;
                await jsonRequest(`${base}/members/${row.dataset.userId}`, { method: 'DELETE' });
                window.location.reload();
            } else if (button.matches('[data-leave-board]')) {
                if (!confirm('Leave this board? You will need a fresh invitation to join again.')) return;
                const data = await jsonRequest(`${base}/leave`, { method: 'POST' });
                window.location.assign(data.redirect);
            } else if (button.matches('[data-revoke-invitation]')) {
                await jsonRequest(`${base}/invitations/${button.dataset.revokeInvitation}`, { method: 'DELETE' });
                button.closest('[data-invitation-row]').remove();
                message('Invitation revoked.');
            }
        } catch (error) { message(error.message); }
        finally { button.disabled = false; }
    });
});
