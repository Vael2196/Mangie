import { jsonRequest } from './http';

async function applyResponse(data) {
    await window.MangieRealtime?.applyMutationResponse?.(data);
}

export function enhanceRemoteColumn(column) {
    if (!column || column.dataset.remoteEnhanced === '1') {
        return;
    }

    column.dataset.remoteEnhanced = '1';
    const columnId = Number(column.dataset.columnId);
    const menu = column.querySelector('[data-column-menu-panel]');
    const menuToggle = column.querySelector('[data-column-menu-toggle]');
    const display = column.querySelector('[data-column-name-display]');
    const editor = column.querySelector('[data-column-name-editor]');
    const input = column.querySelector('[data-column-name-input]');

    menuToggle?.addEventListener('click', event => {
        event.stopPropagation();
        menu?.classList.toggle('hidden');
    });

    display?.addEventListener('click', () => {
        display.classList.add('hidden');
        editor.classList.remove('hidden');
        input.focus();
        input.select();
    });

    input?.addEventListener('keydown', async event => {
        if (event.key === 'Escape') {
            editor.classList.add('hidden');
            display.classList.remove('hidden');
            return;
        }

        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        const data = await jsonRequest(`/columns/${columnId}/name`, {
            method: 'PATCH',
            body: { name: input.value.trim() },
        });
        await applyResponse(data);
    });

    column.querySelectorAll('[data-column-color]').forEach(button => {
        button.addEventListener('click', async () => {
            const data = await jsonRequest(`/columns/${columnId}/color`, {
                method: 'PATCH',
                body: { color: button.dataset.color },
            });
            await applyResponse(data);
        });
    });

    column.querySelector('[data-copy-column]')?.addEventListener('click', async () => {
        const data = await jsonRequest(`/columns/${columnId}/copy`, {
            method: 'POST',
        });
        await applyResponse(data);
    });

    column.querySelector('[data-delete-column]')?.addEventListener('click', async () => {
        if (!window.confirm('Delete this empty column permanently?')) {
            return;
        }

        const data = await jsonRequest(`/columns/${columnId}`, {
            method: 'DELETE',
        });
        await applyResponse(data);
    });

    const section = column.querySelector('.add-task-section');
    const addButton = section?.querySelector('.add-task-btn');
    const taskEditor = section?.querySelector('.new-task-editor');
    const taskInput = section?.querySelector('.new-task-input');
    const taskError = section?.querySelector('.task-error');

    addButton?.addEventListener('click', () => {
        addButton.classList.add('hidden');
        taskEditor.classList.remove('hidden');
        taskInput.focus();
    });

    section?.querySelector('.cancel-add-task')?.addEventListener('click', () => {
        taskInput.value = '';
        taskEditor.classList.add('hidden');
        addButton.classList.remove('hidden');
    });

    const createTask = async () => {
        const title = taskInput.value.trim();

        if (!title) {
            taskError.textContent = 'Please enter a task title.';
            taskError.classList.remove('hidden');
            return;
        }

        const data = await jsonRequest('/tasks/store', {
            method: 'POST',
            body: { title, column_id: columnId },
        });
        await applyResponse(data);
        taskInput.value = '';
        taskEditor.classList.add('hidden');
        addButton.classList.remove('hidden');
    };

    section?.querySelector('.confirm-add-task')?.addEventListener('click', createTask);
    taskInput?.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            void createTask();
        }
    });
}
