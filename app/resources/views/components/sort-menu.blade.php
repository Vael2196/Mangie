@props(['scope'])

@php
    $sortFields = [
        'title' => 'Title',
        'description' => 'Description',
        'priority' => 'Priority',
        'labels' => 'Labels',
        'story_points' => 'Story Points',
        'time_log' => 'Time Log',
    ];
@endphp


<div class="relative">
    <button
        type="button"
        id="task-sort-button-{{ $scope }}"
        class="inline-flex items-center gap-2 rounded-xl
               border border-gray-300 bg-white
               px-3.5 py-2 text-sm font-semibold
               text-gray-600 shadow-sm transition
               hover:border-indigo-300 hover:bg-indigo-50
               hover:text-indigo-600
               dark:border-gray-700 dark:bg-gray-900
               dark:text-gray-300
               dark:hover:border-indigo-700
               dark:hover:bg-indigo-950/40
               dark:hover:text-indigo-400"
    >
        <span class="material-symbols-rounded text-[19px]">
            sort
        </span>

        Sort

        <span class="material-symbols-rounded text-[18px]">
            expand_more
        </span>
    </button>


    <div
        id="task-sort-menu-{{ $scope }}"
        class="absolute right-0 top-full z-50 mt-2 hidden
               w-56 overflow-visible rounded-xl
               border border-gray-200 bg-white p-1.5
               shadow-xl
               dark:border-gray-700 dark:bg-gray-800"
    >
        @foreach ($sortFields as $field => $name)
            <div class="group relative">

                <button
                    type="button"
                    class="flex w-full items-center justify-between
                           rounded-lg px-3 py-2.5 text-left
                           text-sm font-medium text-gray-700
                           transition hover:bg-indigo-50
                           hover:text-indigo-700
                           dark:text-gray-200
                           dark:hover:bg-indigo-950/40
                           dark:hover:text-indigo-300"
                >
                    {{ $name }}

                    <span class="material-symbols-rounded text-[18px]">
                        chevron_right
                    </span>
                </button>


                <div
                    class="pointer-events-none invisible absolute
                           left-full top-0 z-50 w-44
                           pl-2 opacity-0 transition
                           group-hover:pointer-events-auto
                           group-hover:visible group-hover:opacity-100"
                >
                    <div
                        class="rounded-xl border border-gray-200
                               bg-white p-1.5 shadow-xl
                               dark:border-gray-700 dark:bg-gray-800"
                    >
                        <button
                            type="button"
                            data-sort-choice
                            data-sort-field="{{ $field }}"
                            data-sort-direction="asc"
                            class="flex w-full items-center gap-2
                                   rounded-lg px-3 py-2 text-left
                                   text-sm text-gray-700 transition
                                   hover:bg-indigo-50 hover:text-indigo-700
                                   dark:text-gray-200
                                   dark:hover:bg-indigo-950/40
                                   dark:hover:text-indigo-300"
                        >
                            <span class="material-symbols-rounded text-[18px]">
                                arrow_upward
                            </span>

                            Ascending
                        </button>

                        <button
                            type="button"
                            data-sort-choice
                            data-sort-field="{{ $field }}"
                            data-sort-direction="desc"
                            class="flex w-full items-center gap-2
                                   rounded-lg px-3 py-2 text-left
                                   text-sm text-gray-700 transition
                                   hover:bg-indigo-50 hover:text-indigo-700
                                   dark:text-gray-200
                                   dark:hover:bg-indigo-950/40
                                   dark:hover:text-indigo-300"
                        >
                            <span class="material-symbols-rounded text-[18px]">
                                arrow_downward
                            </span>

                            Descending
                        </button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const scope = @js($scope);

    const button = document.getElementById(
        `task-sort-button-${scope}`
    );

    const menu = document.getElementById(
        `task-sort-menu-${scope}`
    );

    if (!button || !menu) {
        return;
    }


    function setCookie(key, value) {
        document.cookie =
            `${scope}_${key}=${encodeURIComponent(value)}; ` +
            `path=/; SameSite=Lax`;
    }


    button.addEventListener('click', event => {
        event.stopPropagation();

        window.dispatchEvent(
            new CustomEvent('mangie:popover-open', {
                detail: menu.id
            })
        );

        menu.classList.toggle('hidden');
    });


    menu.addEventListener('click', event => {
        event.stopPropagation();
    });


    window.addEventListener('mangie:popover-open', event => {
        if (event.detail !== menu.id) {
            menu.classList.add('hidden');
        }
    });


    document.addEventListener('click', () => {
        menu.classList.add('hidden');
    });


    menu
        .querySelectorAll('[data-sort-choice]')
        .forEach(item => {
            item.addEventListener('click', () => {
                setCookie(
                    'sort',
                    item.dataset.sortField
                );

                setCookie(
                    'direction',
                    item.dataset.sortDirection
                );

                window.location.reload();
            });
        });
});
</script>