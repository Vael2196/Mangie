<tr
    {{ $attributes->merge([
        'class' =>
            'group cursor-pointer text-gray-700 transition hover:bg-gray-50
             dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800',
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
    <td
        data-task-title
        class="border-b border-gray-200 py-3 pl-5 pr-3 text-sm font-medium
               dark:border-gray-700"
    >
        {{ $task->title }}
    </td>

    <td class="border-b border-gray-200 py-3 dark:border-gray-700">
        <div class="flex justify-end gap-2 px-4">
            @if((int) $column->board_id !== (int) config('mangie.product_backlog_board_id', 1))
                <x-status-icon name="{{ $column->name }}" />
            @endif

            @if($task->labels)
                <x-status-icon name="{{ $task->labels }}" />
            @endif

            @if($task->priority)
                <x-status-icon name="{{ $task->priority }}" />
            @endif
        </div>
    </td>

    <td class="w-14 border-b border-gray-200 py-2 dark:border-gray-700">
        <button
            type="button"
            id="task-list-menu-{{ $task->id }}"
            data-open-task="{{ $task->id }}"
            class="flex h-9 w-9 items-center justify-center rounded-lg
                   text-gray-400 transition hover:bg-gray-100 hover:text-gray-700
                   dark:hover:bg-gray-700 dark:hover:text-white"
            aria-label="Open task details"
        >
            <span class="material-symbols-rounded text-[20px]">more_horiz</span>
        </button>
    </td>
</tr>
