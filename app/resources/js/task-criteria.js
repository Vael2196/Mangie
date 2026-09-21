import { jsonRequest } from './http';

function readCriteria() {
    const root = document.querySelector('[data-task-criteria]');

    if (!root) {
        return null;
    }

    return {
        scope: root.dataset.taskCriteriaScope,
        priority: root.dataset.priority ?? '',
        label: root.dataset.label ?? '',
        sortField: root.dataset.sortField ?? '',
        sortDirection: root.dataset.sortDirection ?? '',
    };
}

async function saveCriteria(next) {
    await jsonRequest(
        `/task-criteria/${encodeURIComponent(next.scope)}`,
        {
            method: 'PUT',
            body: {
                priority: next.priority || null,
                label: next.label || null,
                sortField: next.sortField || null,
                sortDirection: next.sortDirection || null,
            },
        }
    );

    window.location.reload();
}

function initialisePopover(wrapper, buttonSelector, menuSelector) {
    const button = wrapper.querySelector(buttonSelector);
    const menu = wrapper.querySelector(menuSelector);

    if (!button || !menu) {
        return;
    }

    button.addEventListener('click', event => {
        event.stopPropagation();
        window.dispatchEvent(new CustomEvent('mangie:popover-open', {
            detail: menu.id,
        }));
        menu.classList.toggle('hidden');
    });

    menu.addEventListener('click', event => event.stopPropagation());

    window.addEventListener('mangie:popover-open', event => {
        if (event.detail !== menu.id) {
            menu.classList.add('hidden');
        }
    });

    document.addEventListener('click', () => menu.classList.add('hidden'));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-task-filter]').forEach(wrapper => {
        initialisePopover(
            wrapper,
            '[id^="task-filter-button-"]',
            '[id^="task-filter-menu-"]'
        );
    });

    document.querySelectorAll('[data-task-sort]').forEach(wrapper => {
        initialisePopover(
            wrapper,
            '[id^="task-sort-button-"]',
            '[id^="task-sort-menu-"]'
        );
    });

    document.addEventListener('click', event => {
        const criteria = readCriteria();

        if (!criteria) {
            return;
        }

        const filter = event.target.closest('[data-filter-choice]');

        if (filter) {
            void saveCriteria({
                ...criteria,
                [filter.dataset.filterKey]: filter.dataset.filterValue,
            });
            return;
        }

        const sort = event.target.closest('[data-sort-choice]');

        if (sort) {
            void saveCriteria({
                ...criteria,
                sortField: sort.dataset.sortField,
                sortDirection: sort.dataset.sortDirection,
            });
            return;
        }

        const removeFilter = event.target.closest('[data-remove-filter]');

        if (removeFilter) {
            void saveCriteria({
                ...criteria,
                [removeFilter.dataset.removeFilter]: '',
            });
            return;
        }

        if (event.target.closest('[data-remove-sort]')) {
            void saveCriteria({
                ...criteria,
                sortField: '',
                sortDirection: '',
            });
        }
    });
});
