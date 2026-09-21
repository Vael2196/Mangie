import { applyMutation } from './realtime-reconciler';

const events = [
    'task.created',
    'task.updated',
    'task.deleted',
    'task.moved',
    'column.created',
    'column.renamed',
    'column.colour-changed',
    'column.copied',
    'column.deleted',
    'board.created',
    'board.updated',
    'board.completed',
    'board.deleted',
];

const subscribedBoards = new Set();

function listen(channel) {
    events.forEach(event => {
        channel.listen(`.${event}`, payload => {
            void applyMutation(event, payload);

            if (event === 'board.created') {
                subscribeBoard(payload.entity.id);
            }
        });
    });
}

function subscribeBoard(boardId) {
    boardId = Number(boardId);

    if (!boardId || subscribedBoards.has(boardId)) {
        return;
    }

    subscribedBoards.add(boardId);
    listen(window.Echo.private(`boards.${boardId}`));
}

document.addEventListener('DOMContentLoaded', () => {
    if (!window.Echo) {
        return;
    }

    const root = document.querySelector('[data-task-sync-context]');

    if (!root) {
        return;
    }

    let boardIds = [];

    try {
        boardIds = JSON.parse(root.dataset.realtimeBoardIds ?? '[]');
    } catch (error) {
        console.error('Invalid realtime board IDs.', error);
    }

    boardIds.forEach(subscribeBoard);

    const projectId = Number(root.dataset.realtimeProjectId);

    if (projectId) {
        listen(window.Echo.private(`projects.${projectId}`));
    }
});
