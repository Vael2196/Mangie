<div
    {{ $attributes->merge([
        'class' =>
            'group flex min-h-20 w-full cursor-pointer rounded-xl border
             border-gray-200 bg-white p-3 text-gray-800 shadow-sm
             transition-all duration-200 hover:-translate-y-0.5
             hover:border-indigo-200 hover:shadow-md
             dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100
             dark:hover:border-indigo-800',
        'id' => "task-list-item-$task->id",
        'data-task-id' => $task->id,
        'data-column-id' => $task->column_id,
        'data-task-version' => $task->version ?? 1,
        'data-task-title-value' => $task->title,
        'data-task-description-value' => $task->description ?? '',
        'data-task-priority-value' => $task->priority ?? '',
        'data-task-label-value' => $task->labels ?? '',
        'data-task-story-points-value' => $task->story_points ?? 0,
        'data-task-time-log-value' => $task->time_log ?? 0,
        'data-task-position' => $task->position,
    ]) }}
>
    <div class="min-w-0 flex grow flex-col">
        <p
            data-task-title
            class="pr-2 text-sm font-semibold leading-5 text-gray-800 dark:text-gray-100"
        >
            {{ $task->title }}
        </p>

        <div class="mt-3 flex flex-wrap gap-1.5">
            @if($task->labels)
                <x-status-icon name="{{ $task->labels }}"/>
            @endif

            @if((int) $column->board_id !== (int) config('mangie.product_backlog_board_id', 1))
                <x-status-icon name="{{ $column->name }}"/>
            @endif

            @if($task->priority)
                <x-status-icon name="{{ $task->priority }}"/>
            @endif
        </div>
    </div>

    <div class="ml-2 flex shrink-0 flex-col items-center justify-between">
        <button
            type="button"
            data-open-task="{{ $task->id }}"
            class="flex h-8 w-8 items-center justify-center rounded-lg
                   text-gray-400 transition hover:bg-gray-100 hover:text-gray-700
                   dark:hover:bg-gray-800 dark:hover:text-white"
            aria-label="Open task details"
        >
            <span class="material-symbols-rounded text-[19px]">more_horiz</span>
        </button>

        <span
            class="material-symbols-rounded text-[24px] text-indigo-400"
            title="Assigned user"
        >account_circle</span>
    </div>
</div>
