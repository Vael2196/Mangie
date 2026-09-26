import { jsonRequest } from './http';
import {
    initSortable,
    insertAtPosition,
    refreshAllEmptyMarkers,
    setColumnCount,
    setIssueCount,
} from './task-dnd';
import { enhanceRemoteColumn } from './remote-column-ui';
import { updateUserAvatars } from './user-avatar';

const seenEvents = new Set();

function root() {
    return document.querySelector('[data-task-sync-context]');
}

function context() {
    return root()?.dataset.taskSyncContext;
}

function taskVariant() {
    return document.querySelector('tbody[data-task-list]') ? 'list' : 'card';
}

function taskElement(taskId) {
    return document.querySelector(`[data-task-id="${taskId}"]`);
}

function directTasks(list) {
    return Array.from(list?.querySelectorAll(':scope > [data-task-id]') ?? []);
}

function destination(task) {
    if (context() === 'board') {
        return document.querySelector(
            `[data-task-list][data-column-id="${task.column_id}"]`
        );
    }

    return document.querySelector(
        `[data-task-list][data-board-id="${task.board_id}"]`
    );
}

function matchesCriteria(task) {
    const criteria = document.querySelector('[data-task-criteria]')?.dataset;

    if (!criteria) {
        return true;
    }

    return (!criteria.priority || task.priority === criteria.priority)
        && (!criteria.label || task.labels === criteria.label);
}

function currentVersion(element) {
    return Number(element?.dataset.taskVersion ?? 0);
}

function contextualElement(target, html) {
    const range = document.createRange();
    range.selectNodeContents(target);
    return range.createContextualFragment(html).firstElementChild;
}

function valueFor(element, field) {
    const key = {
        title: 'taskTitleValue',
        description: 'taskDescriptionValue',
        priority: 'taskPriorityValue',
        labels: 'taskLabelValue',
        story_points: 'taskStoryPointsValue',
        time_log: 'taskTimeLogValue',
    }[field];

    return element.dataset[key] ?? '';
}

function insertByCriteria(list, item, task) {
    const criteria = document.querySelector('[data-task-criteria]')?.dataset;
    const field = criteria?.sortField;
    const direction = criteria?.sortDirection;

    if (!field || !direction) {
        insertAtPosition(list, item, task.position);
        return;
    }

    const numeric = ['story_points', 'time_log'].includes(field);
    const nextValue = numeric
        ? Number(task[field] ?? 0)
        : String(task[field] ?? '').toLocaleLowerCase();
    const sibling = directTasks(list).find(candidate => {
        const candidateValue = numeric
            ? Number(valueFor(candidate, field) || 0)
            : String(valueFor(candidate, field)).toLocaleLowerCase();
        const comparison = nextValue < candidateValue
            ? -1
            : nextValue > candidateValue
                ? 1
                : 0;
        return direction === 'asc' ? comparison < 0 : comparison > 0;
    });

    const marker = list.querySelector(':scope > [data-empty-drop-marker]');
    list.insertBefore(item, sibling ?? marker ?? null);
    refreshAllEmptyMarkers();
}

async function fetchTaskElement(task, list) {
    const data = await jsonRequest(
        `/tasks/${task.id}/fragment/${taskVariant()}`
    );
    return contextualElement(list, data.html);
}

function applyCounts(meta = {}) {
    if (meta.source_column_id && meta.source_column_count !== undefined) {
        setColumnCount(meta.source_column_id, meta.source_column_count);
    }

    if (meta.target_column_id && meta.target_column_count !== undefined) {
        setColumnCount(meta.target_column_id, meta.target_column_count);
    }

    if (meta.column_id && meta.column_count !== undefined) {
        setColumnCount(meta.column_id, meta.column_count);
    }

    if (meta.backlog_issue_count !== undefined) {
        setIssueCount(meta.backlog_issue_count);
    }
}

async function upsertTask(payload) {
    const task = payload.entity;
    const existing = taskElement(task.id);

    if (existing && currentVersion(existing) >= Number(task.version)) {
        applyCounts(payload.meta);
        return;
    }

    const list = destination(task);

    if (!list || !matchesCriteria(task)) {
        existing?.remove();
        applyCounts(payload.meta);
        refreshAllEmptyMarkers();
        return;
    }

    const fresh = await fetchTaskElement(task, list);
    existing?.remove();
    insertByCriteria(list, fresh, task);
    applyCounts(payload.meta);
    document.dispatchEvent(new CustomEvent('mangie:task-reconciled', {
        detail: { taskId: task.id },
    }));
}

function deleteTask(payload) {
    const existing = taskElement(payload.entity.id);

    if (existing && currentVersion(existing) < Number(payload.entity.version)) {
        existing.remove();
    }

    applyCounts(payload.meta);
    refreshAllEmptyMarkers();
}

async function fetchColumn(columnId) {
    const data = await jsonRequest(`/columns/${columnId}/fragment`);
    const container = document.getElementById('columns-container');
    return contextualElement(container, data.html);
}

async function upsertColumn(payload, event) {
    if (context() !== 'board') {
        return;
    }

    const column = payload.entity;
    const existing = document.getElementById(`column-${column.id}`);

    if (
        existing
        && Number(existing.dataset.columnVersion ?? 0) >= Number(column.version)
    ) {
        return;
    }

    const fresh = await fetchColumn(column.id);
    fresh.dataset.columnVersion = column.version;
    fresh.dataset.realtimeFragment = '1';

    if (existing) {
        existing.replaceWith(fresh);
    } else if (event === 'column.copied' && payload.meta.source_column_id) {
        document
            .getElementById(`column-${payload.meta.source_column_id}`)
            ?.after(fresh);
    } else {
        const addColumn = document.getElementById('add-column-section');
        const sibling = Array.from(
            addColumn.parentElement.querySelectorAll(':scope > [data-column-id]')
        ).find(element => (
            Number(element.dataset.columnPosition) > Number(column.position)
        ));
        addColumn.parentElement.insertBefore(fresh, sibling ?? addColumn);
    }

    fresh.querySelectorAll('[data-task-list]').forEach(initSortable);
    enhanceRemoteColumn(fresh);
    refreshAllEmptyMarkers();
}

function deleteColumn(payload) {
    document.getElementById(`column-${payload.entity.id}`)?.remove();
}

function applyBoardAppearance(board) {
    const surface = document.querySelector('[data-board-surface]');

    if (!surface) {
        return;
    }

    const color = board.background_color || '#eef2ff';
    const image = board.background_image_url;

    surface.dataset.boardBackgroundColor = color;
    surface.dataset.boardBackgroundImage = image ?? '';
    surface.style.setProperty('--board-background-color', color);
    surface.style.setProperty(
        '--board-background-image',
        image ? `url(${JSON.stringify(image)})` : 'none'
    );
}

async function applyBoardMutation(event, payload) {
    if (event === 'board.completed') {
        for (const task of payload.meta.moved_tasks ?? []) {
            await upsertTask({ entity: task, meta: {} });
        }

        if (payload.meta.backlog_issue_count !== undefined) {
            setIssueCount(payload.meta.backlog_issue_count);
        }
    }

    if (context() === 'board') {
        const currentBoardId = Number(
            JSON.parse(root()?.dataset.realtimeBoardIds ?? '[]')[0]
        );

        if (currentBoardId === Number(payload.entity.id)) {
            if (event === 'board.deleted') {
                window.location.assign('/home');
            } else if (
                event === 'board.updated'
                && payload.meta?.appearance_changed
            ) {
                applyBoardAppearance(payload.entity);
            } else {
                window.location.reload();
            }
        }
        return;
    }

    if (context() === 'boards') {
        const boardId = payload.entity.id;
        const existing = document.querySelectorAll(
            `[data-board-id="${boardId}"]`
        );

        if (event === 'board.deleted' || payload.entity.completed) {
            existing.forEach(element => element.remove());
            return;
        }

        const cardContainer = document.querySelector('.card-view-sprint');
        const listContainer = document.querySelector('.list-view-sprint tbody');

        for (const [variant, container, selector] of [
            ['home-card', cardContainer, `#sprint-card-${boardId}`],
            ['home-list', listContainer, `#sprintRow${boardId}`],
        ]) {
            if (!container) {
                continue;
            }

            try {
                const data = await jsonRequest(
                    `/boards/${boardId}/fragment/${variant}`
                );
                const fresh = contextualElement(container, data.html);
                const current = container.querySelector(selector);
                current ? current.replaceWith(fresh) : container.appendChild(fresh);
            } catch (error) {
                if (error.status !== 403) {
                    throw error;
                }
            }
        }
        return;
    }

    const wrapper = document.getElementById(
        `sprint-loading-board-${payload.entity.id}`
    );

    if (event === 'board.completed' || event === 'board.deleted') {
        wrapper?.remove();
        return;
    }

    const container = document.getElementById('sprint-loading-board-list');

    if (!container) {
        return;
    }

    const variant = taskVariant() === 'list' ? 'backlog-list' : 'backlog-card';

    try {
        const data = await jsonRequest(
            `/boards/${payload.entity.id}/fragment/${variant}`
        );
        const fresh = contextualElement(container, data.html);

        if (wrapper) {
            wrapper.replaceWith(fresh);
        } else {
            const editor = document.getElementById('create-sprint');
            container.insertBefore(fresh, editor ?? null);
        }

        fresh.querySelectorAll('[data-task-list]').forEach(initSortable);
    } catch (error) {
        if (error.status !== 403) {
            throw error;
        }
    }
}

export async function applyMutation(event, payload) {
    if (!event || !payload) {
        return;
    }

    if (payload.event_id && seenEvents.has(payload.event_id)) {
        return;
    }

    if (payload.event_id) {
        seenEvents.add(payload.event_id);
    }

    if (event === 'user.profile-updated') {
        updateUserAvatars(payload.entity);
        return;
    }

    if (['task.created', 'task.updated', 'task.moved'].includes(event)) {
        await upsertTask(payload);
        return;
    }

    if (event === 'task.deleted') {
        deleteTask(payload);
        return;
    }

    if (
        ['column.created', 'column.renamed', 'column.colour-changed', 'column.copied']
            .includes(event)
    ) {
        await upsertColumn(payload, event);
        return;
    }

    if (event === 'column.deleted') {
        deleteColumn(payload);
        return;
    }

    if (event.startsWith('board.')) {
        await applyBoardMutation(event, payload);
    }
}

export async function applyMutationResponse(data) {
    const mutations = data.mutations ?? (
        data.event ? [{ event: data.event, payload: data.payload }] : []
    );

    for (const mutation of mutations) {
        await applyMutation(mutation.event, mutation.payload);
    }
}

window.MangieRealtime = {
    applyMutation,
    applyMutationResponse,
};
