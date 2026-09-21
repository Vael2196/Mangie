import { jsonRequest } from './http';
import { applyMutationResponse } from './realtime-reconciler';

const modal = () => document.getElementById('task-modal');

function content() {
    return modal()?.querySelector('[data-task-modal-content]');
}

function detailForm() {
    return content()?.querySelector('[data-task-detail]');
}

function closeModal() {
    modal()?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');

    if (content()) {
        content().innerHTML = '';
    }
}

export async function openTask(taskId) {
    const taskModal = modal();

    if (!taskModal) {
        return;
    }

    const data = await jsonRequest(`/tasks/${taskId}/detail`);
    content().innerHTML = data.html;
    taskModal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    detailForm()?.querySelector('[data-task-field="title"]')?.focus();
}

export async function refreshOpenTask(taskId) {
    const form = detailForm();

    if (!form || Number(form.dataset.taskId) !== Number(taskId)) {
        return;
    }

    if (form.dataset.localMutation === '1') {
        return;
    }

    const focused = document.activeElement;

    if (focused && form.contains(focused)) {
        form.querySelector('[data-task-error]').textContent =
            'This task changed elsewhere. Close and reopen it before saving.';
        form.querySelector('[data-task-error]').classList.remove('hidden');
        return;
    }

    const data = await jsonRequest(`/tasks/${taskId}/detail`);
    content().innerHTML = data.html;
}

function field(form, name) {
    return form.querySelector(`[data-task-field="${name}"]`);
}

async function saveTask(form) {
    const title = field(form, 'title').value.trim();
    const error = form.querySelector('[data-task-error]');
    const success = form.querySelector('[data-task-success]');
    const button = form.querySelector('[data-save-task]');

    if (!title) {
        error.textContent = 'Task title cannot be empty.';
        error.classList.remove('hidden');
        return;
    }

    error.classList.add('hidden');
    success.classList.add('hidden');
    button.disabled = true;
    button.querySelector('[data-save-text]').textContent = 'Saving...';

    try {
        const data = await jsonRequest(`/tasks/${form.dataset.taskId}`, {
            method: 'PATCH',
            body: {
                column_id: Number(field(form, 'column_id').value),
                title,
                description: field(form, 'description').value.trim() || null,
                assignee: field(form, 'assignee').value
                    ? Number(field(form, 'assignee').value)
                    : null,
                labels: field(form, 'labels').value || null,
                priority: field(form, 'priority').value || null,
                storyPoint: Number(field(form, 'storyPoint').value || 0),
                timeLog: Number(field(form, 'timeLog').value || 0),
                expected_version: Number(form.dataset.taskVersion),
            },
        });

        form.dataset.localMutation = '1';
        await applyMutationResponse(data);
        delete form.dataset.localMutation;
        form.dataset.taskVersion = data.payload.entity.version;
        success.classList.remove('hidden');
    } catch (requestError) {
        error.textContent = requestError.status === 409
            ? 'Someone updated this task first. Close and reopen it to see their changes.'
            : requestError.message;
        error.classList.remove('hidden');
    } finally {
        button.disabled = false;
        button.querySelector('[data-save-text]').textContent = 'Save changes';
    }
}

async function deleteTask(form) {
    if (!window.confirm('Delete this task permanently?')) {
        return;
    }

    const error = form.querySelector('[data-task-error]');

    try {
        const data = await jsonRequest(`/tasks/${form.dataset.taskId}`, {
            method: 'DELETE',
        });
        await applyMutationResponse(data);
        closeModal();
    } catch (requestError) {
        error.textContent = requestError.message;
        error.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    modal()?.querySelector('[data-task-modal-backdrop]')
        ?.addEventListener('click', closeModal);

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal()?.classList.contains('hidden')) {
            closeModal();
        }
    });

    document.addEventListener('click', event => {
        if (event.target.closest('[data-close-task-modal]')) {
            closeModal();
            return;
        }

        const opener = event.target.closest('[data-open-task]');

        if (opener) {
            event.stopPropagation();
            void openTask(opener.dataset.openTask);
            return;
        }

        const task = event.target.closest('[data-task-id]');

        if (
            task
            && task.dataset.wasDragged !== '1'
            && !event.ctrlKey
            && !event.target.closest('button, a, input, textarea, select, label')
        ) {
            void openTask(task.dataset.taskId);
        }

        const deleteButton = event.target.closest('[data-delete-task]');

        if (deleteButton) {
            void deleteTask(deleteButton.closest('[data-task-detail]'));
        }
    });

    document.addEventListener('submit', event => {
        const form = event.target.closest('[data-task-detail]');

        if (!form) {
            return;
        }

        event.preventDefault();
        void saveTask(form);
    });

    document.addEventListener('mangie:task-reconciled', event => {
        void refreshOpenTask(event.detail.taskId);
    });
});
