import { jsonRequest } from './http';
import { applyMutationResponse } from './realtime-reconciler';

const modal = () => document.getElementById('task-modal');
const content = () => modal()?.querySelector('[data-task-modal-content]');
const detailForm = () => content()?.querySelector('[data-task-detail]');

function field(form, name) {
    return form.querySelector(`[data-task-field="${name}"]`);
}

function selectedLabelIds(form) {
    return [...form.querySelectorAll('[data-task-label-option][aria-pressed="true"]')]
        .map(option => Number(option.dataset.labelId));
}

function serializeSections(form) {
    return [...form.querySelectorAll('[data-task-section]')]
        .map(section => ({
            title: section.querySelector('[data-section-title]')?.value.trim() ?? '',
            content: section.querySelector('[data-section-content]')?.value.trim() || null,
        }))
        .filter(section => section.title || section.content);
}

function serializeChecklists(form) {
    return [...form.querySelectorAll('[data-task-checklist]')]
        .map(checklist => ({
            title: checklist.querySelector('[data-checklist-title]')?.value.trim() ?? '',
            items: [...checklist.querySelectorAll('[data-checklist-item]')]
                .map(item => ({
                    content: item.querySelector('[data-checklist-item-content]')?.value.trim() ?? '',
                    is_complete: item.querySelector('[data-checklist-item-complete]')?.checked ?? false,
                }))
                .filter(item => item.content),
        }))
        .filter(checklist => checklist.title || checklist.items.length);
}

function taskPayload(form) {
    return {
        column_id: Number(field(form, 'column_id').value),
        title: field(form, 'title').value.trim(),
        description: field(form, 'description').value.trim() || null,
        assignee: field(form, 'assignee').value
            ? Number(field(form, 'assignee').value)
            : null,
        label_ids: selectedLabelIds(form),
        priority: field(form, 'priority').value || null,
        storyPoint: Number(field(form, 'storyPoint').value || 0),
        timeLog: Number(field(form, 'timeLog').value || 0),
        start_at: field(form, 'start_at').value || null,
        due_at: field(form, 'due_at').value || null,
        due_complete: field(form, 'due_complete').checked,
        sections: serializeSections(form),
        checklists: serializeChecklists(form),
    };
}

function snapshot(form) {
    return JSON.stringify(taskPayload(form));
}

function rememberCleanState(form) {
    form.dataset.initialState = snapshot(form);
    form.dataset.dirty = '0';
}

function updateDirtyState(form) {
    form.dataset.dirty = snapshot(form) === form.dataset.initialState ? '0' : '1';
}

function showTaskError(form, message) {
    const error = form.querySelector('[data-task-error]');

    if (!error) {
        return;
    }

    error.textContent = message;
    error.classList.remove('hidden');
}

function closePopovers(except = null) {
    document.querySelectorAll('[data-task-dropdown-menu], [data-task-label-menu]')
        .forEach(menu => {
            if (menu !== except) {
                menu.classList.add('hidden');
            }
        });
}

function positionPopover(trigger, menu) {
    const rect = trigger.getBoundingClientRect();
    const gutter = 8;
    const width = Math.max(rect.width, 220);
    const resolvedWidth = Math.min(width, window.innerWidth - gutter * 2);

    menu.style.width = `${resolvedWidth}px`;
    menu.style.left = `${Math.min(
        Math.max(gutter, rect.left),
        window.innerWidth - resolvedWidth - gutter
    )}px`;
    menu.style.top = `${rect.bottom + gutter}px`;

    requestAnimationFrame(() => {
        const menuRect = menu.getBoundingClientRect();

        if (menuRect.bottom > window.innerHeight - gutter) {
            menu.style.top = `${Math.max(gutter, rect.top - menuRect.height - gutter)}px`;
        }
    });
}

function openPopover(trigger, menu) {
    const wasOpen = !menu.classList.contains('hidden');
    closePopovers();

    if (wasOpen) {
        return;
    }

    positionPopover(trigger, menu);
    menu.classList.remove('hidden');
}

function closeModal() {
    closePopovers();
    modal()?.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');

    if (content()) {
        content().innerHTML = '';
    }
}

function requestClose({ backdrop = false } = {}) {
    const form = detailForm();

    if (!form || form.dataset.dirty !== '1') {
        closeModal();
        return;
    }

    if (backdrop) {
        showTaskError(form, 'Save or discard your changes before closing this task.');
        form.animate(
            [
                { transform: 'translateX(0)' },
                { transform: 'translateX(-5px)' },
                { transform: 'translateX(5px)' },
                { transform: 'translateX(0)' },
            ],
            { duration: 180 }
        );
        return;
    }

    if (window.confirm('Discard your unsaved task changes?')) {
        closeModal();
    }
}

function updateChecklistProgress(checklist) {
    const items = [...checklist.querySelectorAll('[data-checklist-item]')];
    const complete = items.filter(item =>
        item.querySelector('[data-checklist-item-complete]')?.checked
    ).length;
    const percent = items.length ? Math.round((complete / items.length) * 100) : 0;
    const text = checklist.querySelector('[data-checklist-progress-text]');
    const bar = checklist.querySelector('[data-checklist-progress-bar]');

    if (text) {
        text.textContent = `${percent}%`;
    }

    if (bar) {
        bar.style.width = `${percent}%`;
    }
}

function updateEmptyStates(form) {
    form.querySelector('[data-empty-sections]')?.classList.toggle(
        'hidden',
        Boolean(form.querySelector('[data-task-section]'))
    );
    form.querySelector('[data-empty-checklists]')?.classList.toggle(
        'hidden',
        Boolean(form.querySelector('[data-task-checklist]'))
    );
}

function initializeTaskDetail(form) {
    if (!form) {
        return;
    }

    form.querySelectorAll('[data-task-checklist]').forEach(updateChecklistProgress);
    updateEmptyStates(form);
    rememberCleanState(form);
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

    const form = detailForm();
    initializeTaskDetail(form);
    field(form, 'title')?.focus();
}

export async function refreshOpenTask(taskId) {
    const form = detailForm();

    if (!form || Number(form.dataset.taskId) !== Number(taskId)) {
        return;
    }

    if (form.dataset.localMutation === '1') {
        return;
    }

    if (form.dataset.dirty === '1') {
        showTaskError(
            form,
            'This task changed elsewhere. Save was paused so your local edits are not overwritten.'
        );
        return;
    }

    const data = await jsonRequest(`/tasks/${taskId}/detail`);
    content().innerHTML = data.html;
    initializeTaskDetail(detailForm());
}

async function saveTask(form) {
    const payload = taskPayload(form);
    const error = form.querySelector('[data-task-error]');
    const success = form.querySelector('[data-task-success]');
    const button = form.querySelector('[data-save-task]');

    if (!payload.title) {
        showTaskError(form, 'Task title cannot be empty.');
        return;
    }

    if (payload.sections.some(section => !section.title)) {
        showTaskError(form, 'Every section with content needs a title.');
        return;
    }

    if (payload.checklists.some(checklist => !checklist.title)) {
        showTaskError(form, 'Every checklist needs a title.');
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
                ...payload,
                expected_version: Number(form.dataset.taskVersion),
            },
        });

        form.dataset.localMutation = '1';
        await applyMutationResponse(data);
        delete form.dataset.localMutation;
        form.dataset.taskVersion = data.payload.entity.version;
        rememberCleanState(form);
        success.classList.remove('hidden');
    } catch (requestError) {
        showTaskError(
            form,
            requestError.status === 409
                ? 'Someone updated this task first. Close and reopen it to see their changes.'
                : requestError.message
        );
    } finally {
        delete form.dataset.localMutation;
        button.disabled = false;
        button.querySelector('[data-save-text]').textContent = 'Save changes';
    }
}

async function deleteTask(form) {
    if (!window.confirm('Delete this task permanently?')) {
        return;
    }

    try {
        const data = await jsonRequest(`/tasks/${form.dataset.taskId}`, {
            method: 'DELETE',
        });
        await applyMutationResponse(data);
        closeModal();
    } catch (requestError) {
        showTaskError(form, requestError.message);
    }
}

function renderSelectedLabels(form) {
    const container = form.querySelector('[data-selected-labels]');
    const selected = [...form.querySelectorAll('[data-task-label-option][aria-pressed="true"]')];
    container.innerHTML = '';

    if (!selected.length) {
        const empty = document.createElement('span');
        empty.dataset.noLabels = '';
        empty.className = 'text-gray-400';
        empty.textContent = 'No labels';
        container.appendChild(empty);
        return;
    }

    selected.forEach(option => {
        const chip = document.createElement('span');
        chip.dataset.selectedLabelId = option.dataset.labelId;
        chip.className = 'inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-2 py-0.5 text-xs font-semibold dark:border-gray-700';

        const dot = document.createElement('span');
        dot.className = 'h-2 w-2 rounded-full';
        dot.style.backgroundColor = option.dataset.labelColor;

        chip.append(dot, document.createTextNode(option.dataset.labelName));
        container.appendChild(chip);
    });
}

function labelOption(label, selected = false) {
    const button = document.createElement('button');
    button.type = 'button';
    button.dataset.taskLabelOption = '';
    button.dataset.labelId = label.id;
    button.dataset.labelName = label.name;
    button.dataset.labelColor = label.color;
    button.setAttribute('aria-pressed', selected ? 'true' : 'false');
    button.className = 'flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800';

    const swatch = document.createElement('span');
    swatch.className = 'h-3 w-8 rounded';
    swatch.style.backgroundColor = label.color;

    const name = document.createElement('span');
    name.className = 'min-w-0 flex-1 truncate';
    name.textContent = label.name;

    const check = document.createElement('span');
    check.dataset.labelCheck = '';
    check.className = `material-symbols-rounded text-[18px] text-indigo-600${selected ? '' : ' invisible'}`;
    check.textContent = 'check';

    button.append(swatch, name, check);
    return button;
}

function managedLabelRow(label) {
    const row = document.createElement('div');
    row.dataset.managedLabel = label.id;
    row.className = 'flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-700';

    const swatch = document.createElement('span');
    swatch.className = 'h-5 w-10 rounded';
    swatch.style.backgroundColor = label.color;

    const name = document.createElement('span');
    name.className = 'min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200';
    name.textContent = label.name;

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.dataset.deleteBoardLabel = label.id;
    remove.className = 'flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400';
    remove.setAttribute('aria-label', `Delete ${label.name}`);
    remove.innerHTML = '<span class="material-symbols-rounded text-[18px]">delete</span>';

    row.append(swatch, name, remove);
    return row;
}

function replaceBoardLabels(form, labels) {
    const selectedIds = new Set(selectedLabelIds(form));
    const options = form.querySelector('[data-task-label-options]');
    const manager = form.querySelector('[data-board-label-manager-list]');
    options.innerHTML = '';
    manager.innerHTML = '';

    labels.forEach(label => {
        options.appendChild(labelOption(label, selectedIds.has(Number(label.id))));
        manager.appendChild(managedLabelRow(label));
    });

    if (!labels.length) {
        const empty = document.createElement('p');
        empty.dataset.emptyLabelOptions = '';
        empty.className = 'px-3 py-2 text-xs text-gray-400';
        empty.textContent = 'No labels yet';
        options.appendChild(empty);
    }

    renderSelectedLabels(form);
}

function showLabelManagerError(form, message) {
    const error = form.querySelector('[data-label-manager-error]');
    error.textContent = message;
    error.classList.remove('hidden');
}

function clearLabelManagerError(form) {
    const error = form.querySelector('[data-label-manager-error]');
    error.textContent = '';
    error.classList.add('hidden');
}

async function createBoardLabel(form, button) {
    const picker = form.querySelector('[data-task-label-picker]');
    const nameInput = form.querySelector('[data-new-label-name]');
    const colorInput = form.querySelector('[data-new-label-color]');
    const name = nameInput.value.trim();

    if (!name) {
        showLabelManagerError(form, 'Enter a label name first.');
        return;
    }

    clearLabelManagerError(form);
    button.disabled = true;

    try {
        const data = await jsonRequest(`/boards/${picker.dataset.boardId}/labels`, {
            method: 'POST',
            body: { name, color: colorInput.value },
        });
        form.querySelector('[data-empty-label-options]')?.remove();
        form.querySelector('[data-task-label-options]').appendChild(labelOption(data.label));
        form.querySelector('[data-board-label-manager-list]').appendChild(managedLabelRow(data.label));
        nameInput.value = '';
        nameInput.focus();
    } catch (error) {
        showLabelManagerError(form, error.message);
    } finally {
        button.disabled = false;
    }
}

async function deleteBoardLabel(form, labelId) {
    if (!window.confirm('Delete this label from the board and every task using it?')) {
        return;
    }

    clearLabelManagerError(form);
    const wasDirty = form.dataset.dirty === '1';

    try {
        const data = await jsonRequest(`/board-labels/${labelId}`, { method: 'DELETE' });
        form.querySelector(`[data-managed-label="${labelId}"]`)?.remove();
        form.querySelector(`[data-task-label-option][data-label-id="${labelId}"]`)?.remove();
        document.querySelectorAll(`[data-board-label-id="${labelId}"]`)
            .forEach(element => element.remove());

        if (
            (data.affected_task_ids ?? [])
                .map(Number)
                .includes(Number(form.dataset.taskId))
        ) {
            form.dataset.taskVersion = String(
                Number(form.dataset.taskVersion) + 1
            );
        }

        renderSelectedLabels(form);

        if (wasDirty) {
            updateDirtyState(form);
        } else {
            rememberCleanState(form);
        }

        const criteria = document.querySelector('[data-task-criteria]');
        if (Number(criteria?.dataset.label) === Number(labelId)) {
            window.location.reload();
        }
    } catch (error) {
        showLabelManagerError(form, error.message);
    }
}

async function importLabelList(form, source) {
    const boardId = form.querySelector('[data-task-label-picker]').dataset.boardId;
    clearLabelManagerError(form);

    try {
        const data = await jsonRequest(`/boards/${boardId}/labels/import`, {
            method: 'POST',
            body: source === 'standard'
                ? { source: 'standard' }
                : { source: 'library', library_id: Number(source) },
        });
        replaceBoardLabels(form, data.labels);
    } catch (error) {
        showLabelManagerError(form, error.message);
    }
}

function libraryRow(library) {
    const row = document.createElement('div');
    row.dataset.labelLibrary = library.id;
    row.className = 'flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-700';
    row.innerHTML = '<span class="material-symbols-rounded text-[19px] text-gray-400">bookmarks</span>';

    const name = document.createElement('span');
    name.className = 'min-w-0 flex-1 truncate text-sm font-semibold text-gray-700 dark:text-gray-200';
    name.textContent = library.name;

    const importButton = document.createElement('button');
    importButton.type = 'button';
    importButton.dataset.importLabelLibrary = library.id;
    importButton.className = 'rounded-lg px-2 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50';
    importButton.textContent = 'Import';

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.dataset.deleteLabelLibrary = library.id;
    remove.className = 'flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400';
    remove.setAttribute('aria-label', 'Delete saved list');
    remove.innerHTML = '<span class="material-symbols-rounded text-[17px]">delete</span>';

    row.append(name, importButton, remove);
    return row;
}

async function saveLabelLibrary(form) {
    const boardId = form.querySelector('[data-task-label-picker]').dataset.boardId;
    const input = form.querySelector('[data-label-library-name]');
    const name = input.value.trim();

    if (!name) {
        showLabelManagerError(form, 'Enter a name for the saved label list.');
        return;
    }

    clearLabelManagerError(form);

    try {
        const data = await jsonRequest(`/boards/${boardId}/label-libraries`, {
            method: 'POST',
            body: { name },
        });
        form.querySelector('[data-label-library-list]').appendChild(libraryRow(data.library));
        input.value = '';
    } catch (error) {
        showLabelManagerError(form, error.message);
    }
}

async function deleteLabelLibrary(form, libraryId) {
    if (!window.confirm('Delete this saved label list? Labels already imported to boards will remain.')) {
        return;
    }

    clearLabelManagerError(form);

    try {
        await jsonRequest(`/label-libraries/${libraryId}`, { method: 'DELETE' });
        form.querySelector(`[data-label-library="${libraryId}"]`)?.remove();
    } catch (error) {
        showLabelManagerError(form, error.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    modal()?.querySelector('[data-task-modal-backdrop]')
        ?.addEventListener('click', () => requestClose({ backdrop: true }));

    window.addEventListener('resize', () => closePopovers());
    window.addEventListener('scroll', () => closePopovers(), true);

    document.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || modal()?.classList.contains('hidden')) {
            return;
        }

        const manager = detailForm()?.querySelector('[data-label-manager]:not(.hidden)');

        if (manager) {
            manager.classList.add('hidden');
            return;
        }

        if (document.querySelector('[data-task-dropdown-menu]:not(.hidden), [data-task-label-menu]:not(.hidden)')) {
            closePopovers();
            return;
        }

        requestClose();
    });

    document.addEventListener('input', event => {
        const form = event.target.closest('[data-task-detail]');

        if (form) {
            updateDirtyState(form);
        }
    });

    document.addEventListener('change', event => {
        const form = event.target.closest('[data-task-detail]');

        if (!form) {
            return;
        }

        const checklist = event.target.closest('[data-task-checklist]');
        if (checklist) {
            updateChecklistProgress(checklist);
        }

        updateDirtyState(form);
    });

    document.addEventListener('click', event => {
        const form = detailForm();

        if (
            event.target.closest('[data-task-modal-shell]')
            && !event.target.closest('[data-task-detail]')
        ) {
            requestClose({ backdrop: true });
            return;
        }

        if (event.target.closest('[data-close-task-modal]')) {
            requestClose();
            return;
        }

        const dropdownTrigger = event.target.closest('[data-task-dropdown-trigger]');
        if (dropdownTrigger) {
            event.stopPropagation();
            const dropdown = dropdownTrigger.closest('[data-task-dropdown]');
            openPopover(dropdownTrigger, dropdown.querySelector('[data-task-dropdown-menu]'));
            return;
        }

        const dropdownOption = event.target.closest('[data-task-dropdown-option]');
        if (dropdownOption) {
            const dropdown = dropdownOption.closest('[data-task-dropdown]');
            const input = dropdown.querySelector('input[type="hidden"]');
            input.value = dropdownOption.dataset.value;
            dropdown.querySelector('[data-task-dropdown-value]').textContent = dropdownOption.dataset.label;
            input.dispatchEvent(new Event('change', { bubbles: true }));
            closePopovers();
            return;
        }

        const labelTrigger = event.target.closest('[data-task-label-trigger]');
        if (labelTrigger) {
            event.stopPropagation();
            const picker = labelTrigger.closest('[data-task-label-picker]');
            openPopover(labelTrigger, picker.querySelector('[data-task-label-menu]'));
            return;
        }

        const labelOptionButton = event.target.closest('[data-task-label-option]');
        if (labelOptionButton && form) {
            const selected = labelOptionButton.getAttribute('aria-pressed') !== 'true';
            labelOptionButton.setAttribute('aria-pressed', selected ? 'true' : 'false');
            labelOptionButton.querySelector('[data-label-check]')?.classList.toggle('invisible', !selected);
            renderSelectedLabels(form);
            updateDirtyState(form);
            closePopovers();
            return;
        }

        if (event.target.closest('[data-open-label-manager]') && form) {
            closePopovers();
            form.querySelector('[data-label-manager]').classList.remove('hidden');
            return;
        }

        if (event.target.closest('[data-close-label-manager]') && form) {
            form.querySelector('[data-label-manager]').classList.add('hidden');
            return;
        }

        const createLabel = event.target.closest('[data-create-board-label]');
        if (createLabel && form) {
            void createBoardLabel(form, createLabel);
            return;
        }

        const deleteLabel = event.target.closest('[data-delete-board-label]');
        if (deleteLabel && form) {
            void deleteBoardLabel(form, deleteLabel.dataset.deleteBoardLabel);
            return;
        }

        const importLibrary = event.target.closest('[data-import-label-library]');
        if (importLibrary && form) {
            void importLabelList(form, importLibrary.dataset.importLabelLibrary);
            return;
        }

        if (event.target.closest('[data-save-label-library]') && form) {
            void saveLabelLibrary(form);
            return;
        }

        const deleteLibrary = event.target.closest('[data-delete-label-library]');
        if (deleteLibrary && form) {
            void deleteLabelLibrary(form, deleteLibrary.dataset.deleteLabelLibrary);
            return;
        }

        if (event.target.closest('[data-add-task-section]') && form) {
            const template = content().querySelector('#task-section-template');
            form.querySelector('[data-task-section-list]').appendChild(template.content.cloneNode(true));
            updateEmptyStates(form);
            updateDirtyState(form);
            form.querySelector('[data-task-section-list] [data-task-section]:last-child [data-section-title]')?.focus();
            return;
        }

        const removeSection = event.target.closest('[data-remove-task-section]');
        if (removeSection && form) {
            removeSection.closest('[data-task-section]').remove();
            updateEmptyStates(form);
            updateDirtyState(form);
            return;
        }

        if (event.target.closest('[data-add-task-checklist]') && form) {
            const template = content().querySelector('#task-checklist-template');
            form.querySelector('[data-task-checklist-list]').appendChild(template.content.cloneNode(true));
            updateEmptyStates(form);
            updateDirtyState(form);
            form.querySelector('[data-task-checklist-list] [data-task-checklist]:last-child [data-checklist-title]')?.focus();
            return;
        }

        const removeChecklist = event.target.closest('[data-remove-task-checklist]');
        if (removeChecklist && form) {
            removeChecklist.closest('[data-task-checklist]').remove();
            updateEmptyStates(form);
            updateDirtyState(form);
            return;
        }

        const addChecklistItem = event.target.closest('[data-add-checklist-item]');
        if (addChecklistItem && form) {
            const checklist = addChecklistItem.closest('[data-task-checklist]');
            const template = content().querySelector('#checklist-item-template');
            checklist.querySelector('[data-checklist-items]').appendChild(template.content.cloneNode(true));
            updateChecklistProgress(checklist);
            updateDirtyState(form);
            checklist.querySelector('[data-checklist-item]:last-child [data-checklist-item-content]')?.focus();
            return;
        }

        const removeChecklistItem = event.target.closest('[data-remove-checklist-item]');
        if (removeChecklistItem && form) {
            const checklist = removeChecklistItem.closest('[data-task-checklist]');
            removeChecklistItem.closest('[data-checklist-item]').remove();
            updateChecklistProgress(checklist);
            updateDirtyState(form);
            return;
        }

        const deleteButton = event.target.closest('[data-delete-task]');
        if (deleteButton) {
            void deleteTask(deleteButton.closest('[data-task-detail]'));
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
            return;
        }

        if (!event.target.closest('[data-task-dropdown], [data-task-label-picker]')) {
            closePopovers();
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
