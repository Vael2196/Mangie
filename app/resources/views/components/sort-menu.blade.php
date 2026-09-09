<div id="sort-menu-root" class="relative">

    <button
        type="button"
        id="taskSortButton"
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
            sort
        </span>

        Sort

        <span class="material-symbols-rounded text-[18px] text-gray-400">
            expand_more
        </span>
    </button>


    <div
        id="taskSortMenu"
        class="absolute right-0 top-full z-50 mt-2 hidden
               w-60 rounded-2xl border border-gray-200
               bg-white p-2 shadow-xl
               dark:border-gray-700 dark:bg-gray-800"
    >
        <p
            class="px-3 pb-2 pt-1
                   text-xs font-semibold uppercase tracking-wider
                   text-gray-400"
        >
            Sort tasks by
        </p>


        @foreach ([
            'title' => 'Title',
            'description' => 'Description',
            'priority' => 'Priority',
            'labels' => 'Labels',
            'story_points' => 'Story Points',
            'time_log' => 'Time Log'
        ] as $key => $label)

            <div class="group relative">

                <button
                    type="button"
                    class="flex w-full items-center justify-between
                           rounded-lg px-3 py-2.5
                           text-left text-sm font-medium
                           text-gray-700 transition
                           hover:bg-indigo-50 hover:text-indigo-700
                           dark:text-gray-200
                           dark:hover:bg-indigo-950/50
                           dark:hover:text-indigo-300"
                >
                    {{ $label }}

                    <span
                        class="material-symbols-rounded
                               text-[18px] text-gray-400"
                    >
                        chevron_right
                    </span>
                </button>

                <div
                    class="absolute left-full top-0 z-[60]
                           ml-2 hidden w-44
                           rounded-xl border border-gray-200
                           bg-white p-1.5 shadow-xl
                           group-hover:block
                           group-focus-within:block
                           dark:border-gray-700 dark:bg-gray-800"
                >
                    <button
                        type="button"
                        data-sort-key="{{ $key }}"
                        data-sort-direction="asc"
                        class="sort-choice flex w-full items-center gap-2
                               rounded-lg px-3 py-2.5
                               text-sm font-medium text-gray-700
                               transition hover:bg-indigo-50
                               hover:text-indigo-700
                               dark:text-gray-200
                               dark:hover:bg-indigo-950/50
                               dark:hover:text-indigo-300"
                    >
                        <span class="material-symbols-rounded text-[18px]">
                            arrow_upward
                        </span>

                        Ascending
                    </button>


                    <button
                        type="button"
                        data-sort-key="{{ $key }}"
                        data-sort-direction="desc"
                        class="sort-choice flex w-full items-center gap-2
                               rounded-lg px-3 py-2.5
                               text-sm font-medium text-gray-700
                               transition hover:bg-indigo-50
                               hover:text-indigo-700
                               dark:text-gray-200
                               dark:hover:bg-indigo-950/50
                               dark:hover:text-indigo-300"
                    >
                        <span class="material-symbols-rounded text-[18px]">
                            arrow_downward
                        </span>

                        Descending
                    </button>
                </div>

            </div>
        @endforeach

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('sort-menu-root');
    const button = document.getElementById('taskSortButton');
    const menu = document.getElementById('taskSortMenu');

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
                    menu: 'sort'
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
        if (event.detail.menu !== 'sort') {
            closeMenu();
        }
    });


    document.addEventListener('click', event => {
        if (!root.contains(event.target)) {
            closeMenu();
        }
    });


    document.querySelectorAll('.sort-choice').forEach(choice => {
        choice.addEventListener('click', () => {
            const sortKey = choice.dataset.sortKey;
            const direction = choice.dataset.sortDirection;

            document.cookie =
                `sort=${encodeURIComponent(sortKey)}; path=/; SameSite=Lax`;

            document.cookie =
                `direction=${encodeURIComponent(direction)}; path=/; SameSite=Lax`;

            window.location.reload();
        });
    });
});
</script>