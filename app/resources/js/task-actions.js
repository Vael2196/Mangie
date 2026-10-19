import { jsonRequest } from './http';
import { applyMutationResponse } from './realtime-reconciler';
import { openTask } from './task-ui';

const colors = { gray: '#f1f5f9', blue: '#dbeafe', green: '#dcfce7', yellow: '#fef9c3', orange: '#ffedd5', red: '#fee2e2', purple: '#f3e8ff', pink: '#fce7f3' };
let activeMenu = null;
let epoch = 0;

export function menuPosition(x, y, width, height, viewportWidth, viewportHeight) {
    return { left: Math.max(8, Math.min(x, viewportWidth - width - 8)), top: Math.max(8, Math.min(y, viewportHeight - height - 8)) };
}

function closeMenu({ focus = false } = {}) {
    epoch += 1;
    const previous = activeMenu;
    activeMenu = null;
    previous?.trigger?.setAttribute('aria-expanded', 'false');
    previous?.element.remove();
    if (focus && previous?.trigger?.isConnected) previous.trigger.focus();
}

function notify(message) {
    let node = document.querySelector('[data-task-action-feedback]');
    if (!node) {
        node = document.createElement('p');
        node.dataset.taskActionFeedback = '';
        node.setAttribute('role', 'alert');
        node.className = 'fixed bottom-5 left-1/2 z-[260] w-[min(90vw,28rem)] -translate-x-1/2 rounded-xl border border-red-200 bg-white px-4 py-3 text-sm text-red-700 shadow-xl dark:border-red-900 dark:bg-gray-900 dark:text-red-300';
        document.body.append(node);
    }
    node.textContent = message;
    node.hidden = false;
    window.setTimeout(() => { node.hidden = true; }, 7000);
}

function button(menu, label, icon, action, { danger = false } = {}) {
    const node = document.createElement('button');
    node.type = 'button';
    node.setAttribute('role', 'menuitem');
    node.className = 'flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium hover:bg-gray-100 focus-visible:bg-indigo-50 focus-visible:outline-none dark:hover:bg-gray-800 ' + (danger ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-200');
    const symbol = document.createElement('span');
    symbol.className = 'material-symbols-rounded text-[19px]';
    symbol.setAttribute('aria-hidden', 'true');
    symbol.textContent = icon;
    node.append(symbol, document.createTextNode(label));
    node.addEventListener('click', event => { event.stopPropagation(); action(node); });
    menu.append(node);
    return node;
}

function fit(state) {
    const rect = state.element.getBoundingClientRect();
    const position = menuPosition(state.x, state.y, rect.width, rect.height, window.innerWidth, window.innerHeight);
    state.element.style.left = `${position.left}px`;
    state.element.style.top = `${position.top}px`;
}

async function mutate(state, operation, { keepOpen = false } = {}) {
    if (state.busy) return;
    state.busy = true;
    state.element.setAttribute('aria-busy', 'true');
    try {
        const response = await operation();
        if (response.payload || response.mutations) await applyMutationResponse(response);
        if (!keepOpen) closeMenu();
        return response;
    } catch (error) {
        state.error.textContent = error.message;
        state.error.hidden = false;
        fit(state);
    } finally {
        state.busy = false;
        state.element.removeAttribute('aria-busy');
    }
}

function renderMenu(state, data) {
    const menu = state.element;
    menu.replaceChildren();
    const title = document.createElement('p');
    title.className = 'truncate border-b border-gray-100 px-3 py-2 text-xs font-semibold text-gray-500 dark:border-gray-800';
    title.textContent = data.task.title;
    menu.append(title);
    button(menu, 'Details', 'open_in_full', () => {
        closeMenu();
        void openTask(state.id).catch(error => notify(error.message));
    });
    const update = values => jsonRequest(`/tasks/${state.id}/actions`, { method: 'PATCH', body: { expected_version: data.task.version, ...values } });
    if (data.can_edit) {
        const moveOptions = document.createElement('div');
        moveOptions.className = 'ml-3 border-l border-gray-200 pl-2 dark:border-gray-700';
        moveOptions.hidden = true;
        const moveButton = button(menu, 'Move to', 'drive_file_move', node => {
            moveOptions.hidden = !moveOptions.hidden;
            node.setAttribute('aria-expanded', String(!moveOptions.hidden));
            fit(state);
        });
        moveButton.setAttribute('aria-expanded', 'false');
        data.columns.forEach(column => {
            const option = button(moveOptions, column.name, column.id === data.task.column_id ? 'check' : 'view_column', () => {
                void mutate(state, () => jsonRequest(`/tasks/${state.id}/move`, { method: 'PATCH', body: {
                    target_column_id: column.id, target_position: 2147483647, expected_version: data.task.version,
                } }));
            });
            option.disabled = column.id === data.task.column_id;
            option.classList.add('disabled:opacity-40');
        });
        menu.append(moveOptions);
        button(menu, data.joined ? 'Leave task' : 'Join', 'person_add', () => { void mutate(state, () => update({ joined: !data.joined })); });
        button(menu, 'Copy', 'content_copy', () => {
            void mutate(state, () => jsonRequest(`/tasks/${state.id}/copy`, { method: 'POST', body: { expected_version: data.task.version } }));
        });
    }
    const watch = button(menu, 'Watch', data.watching ? 'check_box' : 'check_box_outline_blank', node => {
        void mutate(state, async () => {
            const response = await jsonRequest(`/tasks/${state.id}/watch`, { method: 'PUT', body: { watching: !data.watching } });
            data.watching = response.watching;
            node.setAttribute('aria-checked', String(data.watching));
            node.querySelector('span').textContent = data.watching ? 'check_box' : 'check_box_outline_blank';
            return response;
        }, { keepOpen: true });
    });
    watch.setAttribute('role', 'menuitemcheckbox');
    watch.setAttribute('aria-checked', String(data.watching));
    if (data.can_edit) {
        const palette = document.createElement('div');
        palette.hidden = true;
        palette.className = 'task-color-options';
        button(menu, 'Change colour', 'palette', () => { palette.hidden = !palette.hidden; fit(state); });
        Object.entries(colors).forEach(([value, color]) => {
            const swatch = document.createElement('button');
            swatch.type = 'button';
            swatch.className = 'h-8 w-8 rounded-full border-2 border-white shadow-sm ring-1 ring-gray-200 focus-visible:ring-2 focus-visible:ring-indigo-500';
            swatch.style.backgroundColor = color;
            swatch.setAttribute('aria-label', value);
            swatch.setAttribute('aria-pressed', String(data.task.color === value));
            swatch.addEventListener('click', () => { void mutate(state, () => update({ color: value })); });
            palette.append(swatch);
        });
        const reset = button(palette, 'Default colour', 'restart_alt', () => { void mutate(state, () => update({ color: null })); });
        reset.style.gridColumn = '1 / -1';
        menu.append(palette);
        button(menu, 'Delete', 'delete', () => {
            if (state.confirmDelete) {
                void mutate(state, () => jsonRequest(`/tasks/${state.id}`, { method: 'DELETE', body: { expected_version: data.task.version } }));
                return;
            }
            state.confirmDelete = true;
            const confirm = button(menu, 'Confirm permanent deletion', 'delete_forever', () => {
                void mutate(state, () => jsonRequest(`/tasks/${state.id}`, { method: 'DELETE', body: { expected_version: data.task.version } }));
            }, { danger: true });
            fit(state);
            confirm.focus();
        }, { danger: true });
    }
    state.error = document.createElement('p');
    state.error.hidden = true;
    state.error.className = 'px-3 py-2 text-xs text-red-600 dark:text-red-400';
    state.error.setAttribute('role', 'alert');
    menu.append(state.error);
    fit(state);
    menu.querySelector('button')?.focus({ preventScroll: true });
}

async function showMenu(id, { x, y, trigger = null }) {
    closeMenu();
    const requestEpoch = epoch;
    const element = document.createElement('div');
    element.dataset.taskActionMenu = '';
    element.setAttribute('role', 'menu');
    element.setAttribute('aria-label', 'Task actions');
    element.className = 'fixed z-[240] max-h-[calc(100dvh-1rem)] w-64 max-w-[calc(100vw-1rem)] overflow-y-auto rounded-2xl border border-gray-200 bg-white p-1.5 shadow-2xl dark:border-gray-700 dark:bg-gray-900';
    element.textContent = 'Loading task actions…';
    document.body.append(element);
    const state = { id: Number(id), element, x, y, trigger, busy: false };
    activeMenu = state;
    trigger?.setAttribute('aria-expanded', 'true');
    fit(state);
    try {
        const data = await jsonRequest(`/tasks/${state.id}/actions`);
        if (epoch === requestEpoch) renderMenu(state, data);
    } catch (error) {
        if (epoch !== requestEpoch) return;
        closeMenu();
        notify(error.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-task-menu-trigger]');
        if (trigger) {
            event.preventDefault();
            event.stopPropagation();
            if (activeMenu?.trigger === trigger) { closeMenu(); return; }
            const rect = trigger.getBoundingClientRect();
            void showMenu(trigger.dataset.taskMenuTrigger, { x: rect.right - 256, y: rect.bottom + 5, trigger });
            return;
        }
        if (activeMenu && !event.target.closest('[data-task-action-menu]')) closeMenu();
    });
    document.addEventListener('contextmenu', event => {
        const task = event.target.closest('[data-task-id]');
        if (!task || event.target.closest('[data-task-detail], input, textarea')) { closeMenu(); return; }
        event.preventDefault();
        event.stopImmediatePropagation();
        void showMenu(task.dataset.taskId, { x: event.clientX, y: event.clientY, trigger: task.querySelector('[data-task-menu-trigger]') });
    }, true);
    document.addEventListener('keydown', event => {
        if (!activeMenu) return;
        if (event.key === 'Escape') { event.preventDefault(); event.stopImmediatePropagation(); closeMenu({ focus: true }); return; }
        if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
            const buttons = [...activeMenu.element.querySelectorAll('button:not(:disabled)')].filter(node => !node.closest('[hidden]'));
            const index = buttons.indexOf(document.activeElement);
            const next = event.key === 'Home' ? 0 : event.key === 'End' ? buttons.length - 1
                : (index + (event.key === 'ArrowDown' ? 1 : -1) + buttons.length) % buttons.length;
            event.preventDefault(); buttons[next]?.focus();
        }
    }, true);
    document.addEventListener('change', async event => {
        const checkbox = event.target.closest('[data-task-card-complete]');
        if (!checkbox) return;
        const task = checkbox.closest('[data-task-id]');
        checkbox.disabled = true;
        try {
            const data = await jsonRequest(`/tasks/${task.dataset.taskId}/actions`, { method: 'PATCH', body: {
                completed: checkbox.checked, expected_version: Number(task.dataset.taskVersion),
            } });
            await applyMutationResponse(data);
        } catch (error) { checkbox.checked = !checkbox.checked; notify(error.message); }
        finally { checkbox.disabled = false; }
    });
    window.addEventListener('resize', () => closeMenu());
    window.addEventListener('scroll', event => { if (!event.target.closest?.('[data-task-action-menu]')) closeMenu(); }, true);
    document.addEventListener('dragstart', () => closeMenu());
});
