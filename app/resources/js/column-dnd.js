import Sortable from 'sortablejs';
import { jsonRequest } from './http';

let pendingOrder = null;
let saving = false;
let dragging = false;
const root = () => document.querySelector('[data-task-sync-context="board"]');
const container = () => document.getElementById('columns-container');
const columns = () => [...(container()?.querySelectorAll(':scope > [data-column-id]') ?? [])];

export function applyColumnOrder(payload) {
    const board = root();
    const list = container();
    if (!board || !list || Number(board.dataset.boardId) !== Number(payload.entity.id)) return;
    if (saving || dragging) { pendingOrder = payload; return; }
    if (Number(payload.entity.version) < Number(board.dataset.boardVersion)) return;
    const marker = document.getElementById('add-column-section');
    for (const [index, id] of (payload.meta?.column_order ?? []).entries()) {
        const column = list.querySelector(`:scope > [data-column-id="${Number(id)}"]`);
        if (column) {
            list.insertBefore(column, marker);
            column.dataset.columnPosition = index + 1;
        }
    }
    board.dataset.boardVersion = payload.entity.version;
}

async function saveOrder(previous, sortable) {
    const board = root();
    const status = document.querySelector('[data-column-order-status]');
    saving = true;
    sortable.option('disabled', true);
    status?.classList.add('hidden');
    try {
        const data = await jsonRequest(`/boards/${board.dataset.boardId}/columns/order`, {
            method: 'PATCH', body: {
                column_ids: columns().map(column => Number(column.dataset.columnId)),
                expected_version: Number(board.dataset.boardVersion),
                expected_order: previous.map(column => Number(column.dataset.columnId)),
            },
        });
        saving = false;
        applyColumnOrder(data.payload);
    } catch (error) {
        const marker = document.getElementById('add-column-section');
        previous.forEach(column => container().insertBefore(column, marker));
        if (status) { status.textContent = error.message; status.classList.remove('hidden'); }
    } finally {
        saving = false;
        sortable.option('disabled', false);
        if (pendingOrder) { const payload = pendingOrder; pendingOrder = null; applyColumnOrder(payload); }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (!container() || root()?.dataset.canEdit !== 'true') return;
    let previous = [];
    const sortable = new Sortable(container(), {
        draggable: '> [data-column-id]', handle: '[data-column-drag-handle]',
        animation: 180, direction: 'horizontal', ghostClass: 'column-sortable-ghost',
        filter: 'input, textarea, select', preventOnFilter: false,
        onStart() { previous = columns(); dragging = true; },
        onEnd(event) {
            dragging = false;
            if (event.oldDraggableIndex !== event.newDraggableIndex) void saveOrder(previous, sortable);
            else if (pendingOrder) { const payload = pendingOrder; pendingOrder = null; applyColumnOrder(payload); }
        },
    });
    container().addEventListener('keydown', event => {
        const handle = event.target.closest('[data-column-drag-handle]');
        if (!handle || saving || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        const before = columns();
        const column = handle.closest('[data-column-id]');
        const index = before.indexOf(column);
        const sibling = before[index + (event.key === 'ArrowLeft' ? -1 : 1)];
        if (!sibling) return;
        if (event.key === 'ArrowLeft') sibling.before(column); else sibling.after(column);
        void saveOrder(before, sortable).finally(() => handle.focus());
    });
});
