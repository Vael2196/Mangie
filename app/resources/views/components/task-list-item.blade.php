<tr
    {{ $attributes->merge([
        'class' =>
            'group mangie-task-row cursor-pointer text-gray-700 transition hover:bg-gray-50
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
    @if($task->color) style="--task-card-color: {{ config('mangie.task_colors.'.$task->color) }}" data-task-colored @endif
>
    <td
        data-task-title
        class="border-b border-gray-200 py-3 pl-5 pr-3 text-sm font-medium
               dark:border-gray-700"
    >
        <input type="checkbox" data-task-card-complete="{{ $task->id }}" @checked($task->completed_at) @disabled(!auth()->user()->can('update', $column->board)) class="task-completion-control {{ $task->completed_at ? 'is-complete' : '' }} mr-1 h-4 w-4 rounded-full border-gray-300 text-emerald-600 focus:ring-emerald-500" aria-label="Mark task {{ $task->completed_at ? 'incomplete' : 'complete' }}">
        @if($task->cover_image_url)
            <img src="{{ $task->cover_image_url }}" alt="Task cover" class="mr-2 inline-block h-9 w-12 rounded-md object-cover" loading="lazy" draggable="false">
        @endif
        {{ $task->title }}
    </td>

    <td class="border-b border-gray-200 py-3 dark:border-gray-700">
        <div class="task-row-badges flex flex-wrap justify-end gap-2 px-4">
            @if($task->users->isNotEmpty())
                <div class="mr-1 flex -space-x-1.5" aria-label="Assigned users">
                    @foreach($task->users->take(2) as $assignee)
                        <x-user-avatar
                            :user="$assignee"
                            size="xs"
                            ring
                            title="{{ $assignee->name }}"
                        />
                    @endforeach
                </div>
            @endif

            @if(!$column->board->isBacklog())
                <x-status-icon name="{{ $column->name }}" />
            @endif

            @foreach($task->boardLabels as $label)
                <x-status-icon
                    :name="$label->name"
                    :color="$label->color"
                    data-board-label-id="{{ $label->id }}"
                />
            @endforeach

            @if($task->priority)
                <x-status-icon name="{{ $task->priority }}" />
            @endif
        </div>
    </td>

    <td class="w-14 border-b border-gray-200 py-2 dark:border-gray-700">
        <button
            type="button"
            id="task-list-menu-{{ $task->id }}"
            data-task-menu-trigger="{{ $task->id }}"
            class="task-menu-trigger flex h-9 w-9 items-center justify-center rounded-lg
                   text-gray-400 transition hover:bg-gray-100 hover:text-gray-700
                   dark:hover:bg-gray-700 dark:hover:text-white"
            aria-label="Task actions" aria-haspopup="menu" aria-expanded="false"
        >
            <span class="material-symbols-rounded text-[20px]">more_horiz</span>
        </button>
    </td>
</tr>
