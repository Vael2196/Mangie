import { applyMutation } from './realtime-reconciler';
import { jsonRequest } from './http';

const events = [
    'task.created', 'task.updated', 'task.deleted', 'task.moved',
    'column.created', 'column.renamed', 'column.colour-changed', 'column.copied', 'column.deleted',
    'columns.reordered', 'board.created', 'board.updated', 'board.completed', 'board.deleted',
    'board.membership-changed', 'user.profile-updated',
];

// Broadcasts contain IDs only. HTTP reads check current membership again.
async function receive(event, invalidation) {
    const id = Number(invalidation.entity?.id);
    if (!id) return;
    const type = invalidation.entity_type;
    const minimal = { ...invalidation, entity: { id, version: Number.MAX_SAFE_INTEGER }, meta: {} };
    const currentBoard = document.querySelector('[data-task-sync-context="board"]');
    const membersPage = document.querySelector('[data-board-members]');
    if (event === 'board.membership-changed') {
        if (Number(currentBoard?.dataset.boardId ?? membersPage?.dataset.boardId) === id) {
            window.location.reload();
            return;
        }
        event = 'board.updated';
    }
    if (event.endsWith('.deleted')) {
        minimal.meta = (await jsonRequest('/realtime/summary')).meta;
        await applyMutation(event, minimal);
        return;
    }
    if (event === 'board.completed' && document.querySelector('[data-task-sync-context]')) {
        window.location.reload();
        return;
    }
    try {
        const data = await jsonRequest(`/realtime/entities/${type}/${id}`);
        data.payload.event_id = invalidation.event_id;
        await applyMutation(event, data.payload);
    } catch (error) {
        if ([403, 404].includes(error.status)) {
            minimal.meta = (await jsonRequest('/realtime/summary')).meta;
            if (type === 'task') {
                await applyMutation('task.deleted', minimal);
                const open = document.querySelector(`[data-task-detail][data-task-id="${id}"]`);
                if (open) {
                    document.getElementById('task-modal')?.classList.add('hidden');
                    open.remove();
                    document.body.classList.remove('overflow-hidden');
                }
            } else if (type === 'board') {
                await applyMutation('board.deleted', minimal);
            } else if (type === 'column') {
                await applyMutation('column.deleted', minimal);
            }
        } else {
            console.error('Could not refresh a realtime update.', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const userId = Number(document.querySelector('meta[name="current-user-id"]')?.content);
    if (!window.Echo || !userId) return;
    const channel = window.Echo.private(`users.${userId}`);
    channel.error(error => console.error('Could not connect to realtime updates.', error));
    let pending = Promise.resolve();
    events.forEach(event => channel.listen(`.${event}`, payload => {
        pending = pending.then(() => receive(event, payload)).catch(error => {
            console.error('Realtime update failed.', error);
        });
    }));
});
