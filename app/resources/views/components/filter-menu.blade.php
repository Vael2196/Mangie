<div id="filter-menu-root" class="relative">

    <button
        type="button"
        id="taskFilterButton"
        class="inline-flex items-center gap-2 rounded-xl
               border border-gray-200 bg-white
               px-3.5 py-2.5 text-sm font-semibold
               text-gray-600 shadow-sm transition
               hover:border-indigo-200 hover:bg-indigo-50
               hover:text-indigo-600
               dark:border-gray-700 dark:bg-gray-900
               dark:text-gray-300
               dark:hover:border-indigo-800
               dark:hover:bg-indigo-950/40
               dark:hover:text-indigo-300"
    >
        <span class="material-symbols-rounded text-[19px]">
            filter_alt
        </span>

        Filter

        <span class="material-symbols-rounded text-[18px] text-gray-400">
            expand_more
        </span>
    </button>


    <div
        id="taskFilterMenu"
        class="absolute right-0 top-full z-50 mt-2 hidden
               w-52 rounded-2xl border border-gray-200
               bg-white p-2 shadow-xl
               dark:border-gray-700 dark:bg-gray-800"
    >
        <p
            class="px-3 pb-2 pt-1
                   text-xs font-semibold uppercase tracking-wider
                   text-gray-400"
        >
            Filter tasks
        </p>

        <div class="group relative">

            <button
                type="button"
                class="flex w-full items-center justify-between
                       rounded-lg px-3 py-2.5
                       text-sm font-medium text-gray-700
                       transition hover:bg-indigo-50
                       hover:text-indigo-700
                       dark:text-gray-200
                       dark:hover:bg-indigo-950/50
                       dark:hover:text-indigo-300"
            >
                Priority

                <span class="material-symbols-rounded text-[18px] text-gray-400">
                    chevron_right
                </span>
            </button>


            <div
                class="absolute left-full top-0 z-[60]
                       ml-2 hidden w-40
                       rounded-xl border border-gray-200
                       bg-white p-1.5 shadow-xl
                       group-hover:block
                       group-focus-within:block
                       dark:border-gray-700 dark:bg-gray-800"
            >
                @foreach (['Low', 'Medium', 'High'] as $priority)
                    <button
                        type="button"
                        data-filter-key="priority"
                        data-filter-value="{{ $priority }}"
                        class="filter-choice block w-full rounded-lg
                               px-3 py-2.5 text-left
                               text-sm font-medium text-gray-700
                               transition hover:bg-indigo-50
                               hover:text-indigo-700
                               dark:text-gray-200
                               dark:hover:bg-indigo-950/50
                               dark:hover:text-indigo-300"
                    >
                        {{ $priority }}
                    </button>
                @endforeach
            </div>

        </div>


        <div class="group relative">

            <button
                type="button"
                class="flex w-full items-center justify-between
                       rounded-lg px-3 py-2.5
                       text-sm font-medium text-gray-700
                       transition hover:bg-indigo-50
                       hover:text-indigo-700
                       dark:text-gray-200
                       dark:hover:bg-indigo-950/50
                       dark:hover:text-indigo-300"
            >
                Label

                <span class="material-symbols-rounded text-[18px] text-gray-400">
                    chevron_right
                </span>
            </button>


            <div
                class="absolute left-full top-0 z-[60]
                       ml-2 hidden w-40
                       rounded-xl border border-gray-200
                       bg-white p-1.5 shadow-xl
                       group-hover:block
                       group-focus-within:block
                       dark:border-gray-700 dark:bg-gray-800"
            >
                @foreach ([
                    'API',
                    'Backend',
                    'Frontend',
                    'UI/UX',
                    'Database'
                ] as $label)

                    <button
                        type="button"
                        data-filter-key="label"
                        data-filter-value="{{ $label }}"
                        class="filter-choice block w-full rounded-lg
                               px-3 py-2.5 text-left
                               text-sm font-medium text-gray-700
                               transition hover:bg-indigo-50
                               hover:text-indigo-700
                               dark:text-gray-200
                               dark:hover:bg-indigo-950/50
                               dark:hover:text-indigo-300"
                    >
                        {{ $label }}
                    </button>

                @endforeach
            </div>

        </div>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('filter-menu-root');
    const button = document.getElementById('taskFilterButton');
    const menu = document.getElementById('taskFilterMenu');

    if (!root || !button || !menu) {
        return;
    }


    function closeMenu() {
        menu.classList.add('hidden');
    }


    button.addEventListener('click', event => {
        event.stopPropagation();

        const opening = menu.classList.contains('hidden');

        window.dispatchEvent(
            new CustomEvent('mangie-menu-open', {
                detail: {
                    menu: 'filter'
                }
            })
        );

        if (opening) {
            menu.classList.remove('hidden');
        } else {
            closeMenu();
        }
    });


    window.addEventListener('mangie-menu-open', event => {
        if (event.detail.menu !== 'filter') {
            closeMenu();
        }
    });


    document.addEventListener('click', event => {
        if (!root.contains(event.target)) {
            closeMenu();
        }
    });


    document.querySelectorAll('.filter-choice').forEach(choice => {
        choice.addEventListener('click', () => {
            const key = choice.dataset.filterKey;
            const value = choice.dataset.filterValue;

            document.cookie =
                `${key}=${encodeURIComponent(value)}; path=/; SameSite=Lax`;

            window.location.reload();
        });
    });
});
</script>