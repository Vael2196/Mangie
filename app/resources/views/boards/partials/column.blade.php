<x-task-column :column="$column" :editable="$editable">
    <div
        id="task-list-{{ $column->id }}"
        class="task-list min-h-[72px] space-y-3 rounded-xl transition"
        data-task-list
        data-column-id="{{ $column->id }}"
        data-board-id="{{ $board->id }}"
        data-reorder="{{ $criteriaActive ? 'false' : 'true' }}"
    >
        @foreach($column->tasks as $task)
            @include('boards.partials.task-card', ['task' => $task])
        @endforeach

        <div
            data-empty-drop-marker
            class="{{ $column->tasks->isEmpty() ? '' : 'hidden' }}
                   pointer-events-none flex min-h-[58px] items-center justify-center
                   rounded-xl border-2 border-dashed border-gray-200 px-3
                   text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500"
        >
            Drop a task here
        </div>
    </div>

    @if($editable)
        <div class="add-task-section mt-3" data-column-id="{{ $column->id }}">
            <button
                type="button"
                class="add-task-btn flex w-full items-center gap-2 rounded-lg px-2.5 py-2
                       text-sm font-medium text-gray-500 transition hover:bg-gray-200/80
                       hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700
                       dark:hover:text-gray-200"
            >
                <span class="material-symbols-rounded text-[19px]">add</span>
                Add a task
            </button>

            <div class="new-task-editor hidden">
                <textarea
                    class="new-task-input min-h-[90px] w-full resize-none rounded-xl
                           border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-900
                           shadow-sm focus:border-indigo-500 focus:ring-indigo-500
                           dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                    maxlength="255"
                    rows="3"
                    placeholder="Enter a title for this task..."
                ></textarea>
                <div class="mt-2 flex items-center gap-2">
                    <button
                        type="button"
                        class="confirm-add-task rounded-lg bg-indigo-600 px-3 py-2
                               text-sm font-semibold text-white hover:bg-indigo-500"
                    >Add task</button>
                    <button
                        type="button"
                        class="cancel-add-task flex h-9 w-9 items-center justify-center
                               rounded-lg text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700"
                        aria-label="Cancel"
                    >
                        <span class="material-symbols-rounded text-[21px]">close</span>
                    </button>
                </div>
                <p class="task-error mt-2 hidden text-xs text-red-600 dark:text-red-400"></p>
            </div>
        </div>
    @endif
</x-task-column>
