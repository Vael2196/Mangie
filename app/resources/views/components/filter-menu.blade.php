@props(['scope'])

<div class="relative" data-task-filter data-scope="{{ $scope }}">
    <button
        type="button"
        id="task-filter-button-{{ $scope }}"
        class="inline-flex items-center gap-2 rounded-xl
               border border-gray-300 bg-white
               px-3.5 py-2 text-sm font-semibold
               text-gray-600 shadow-sm transition
               hover:border-indigo-300 hover:bg-indigo-50
               hover:text-indigo-600
               dark:border-gray-700 dark:bg-gray-900
               dark:text-gray-300 dark:hover:border-indigo-700
               dark:hover:bg-indigo-950/40
               dark:hover:text-indigo-400"
    >
        <span class="material-symbols-rounded text-[19px]">
            filter_alt
        </span>

        Filter

        <span class="material-symbols-rounded text-[18px]">
            expand_more
        </span>
    </button>


    <div
        id="task-filter-menu-{{ $scope }}"
        class="absolute right-0 top-full z-50 mt-2 hidden
               w-52 overflow-visible rounded-xl
               border border-gray-200 bg-white p-1.5
               shadow-xl
               dark:border-gray-700 dark:bg-gray-800"
    >

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
                <span class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-[18px]">
                        priority_high
                    </span>

                    Priority
                </span>

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
                    @foreach (['Low', 'Medium', 'High'] as $priority)
                        <button
                            type="button"
                            data-filter-choice
                            data-filter-key="priority"
                            data-filter-value="{{ $priority }}"
                            class="block w-full rounded-lg
                                   px-3 py-2 text-left text-sm
                                   text-gray-700 transition
                                   hover:bg-indigo-50 hover:text-indigo-700
                                   dark:text-gray-200
                                   dark:hover:bg-indigo-950/40
                                   dark:hover:text-indigo-300"
                        >
                            {{ $priority }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>


        {{-- Labels --}}
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
                <span class="flex items-center gap-2">
                    <span class="material-symbols-rounded text-[18px]">
                        label
                    </span>

                    Label
                </span>

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
                    @foreach (['API', 'Backend', 'Frontend', 'UI/UX', 'Database'] as $label)
                        <button
                            type="button"
                            data-filter-choice
                            data-filter-key="label"
                            data-filter-value="{{ $label }}"
                            class="block w-full rounded-lg
                                   px-3 py-2 text-left text-sm
                                   text-gray-700 transition
                                   hover:bg-indigo-50 hover:text-indigo-700
                                   dark:text-gray-200
                                   dark:hover:bg-indigo-950/40
                                   dark:hover:text-indigo-300"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
