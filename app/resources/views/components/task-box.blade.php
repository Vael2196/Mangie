@php
    $taskColor = config('mangie.task_colors.'.$task->color);
    $canEditTask = auth()->user()->can('update', $column->board);
@endphp
<div
    {{ $attributes->merge([
        'class' => 'group mangie-task-card relative flex w-full cursor-pointer flex-col overflow-hidden rounded-xl border border-gray-200 bg-white text-gray-800 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
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
    @if($taskColor) style="--task-card-color: {{ $taskColor }}" data-task-colored @endif
>
    @if($task->cover_image_url)
        <img src="{{ $task->cover_image_url }}" alt="Task cover" class="h-36 w-full object-cover" loading="lazy" draggable="false">
    @endif
    <button type="button" data-task-menu-trigger="{{ $task->id }}" draggable="false" class="task-menu-trigger absolute right-2 top-2 z-10 flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200/70 bg-white/95 text-gray-600 shadow-sm backdrop-blur hover:bg-white hover:text-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-indigo-500 dark:border-gray-600 dark:bg-gray-900/95 dark:text-gray-200" aria-label="Task actions" aria-haspopup="menu" aria-expanded="false">
        <span class="material-symbols-rounded text-[20px]">more_horiz</span>
    </button>
    <div class="min-w-0 space-y-3 p-3">
        <div class="flex items-start gap-2 pr-7">
            <input type="checkbox" data-task-card-complete="{{ $task->id }}" @checked($task->completed_at) @disabled(!$canEditTask) class="task-completion-control {{ $task->completed_at ? 'is-complete' : '' }} mt-0.5 h-4 w-4 shrink-0 rounded-full border-gray-300 text-emerald-600 focus:ring-emerald-500" aria-label="Mark task {{ $task->completed_at ? 'incomplete' : 'complete' }}">
            <p data-task-title class="min-w-0 break-words text-sm font-semibold leading-5">{{ $task->title }}</p>
        </div>
        <div class="flex flex-wrap gap-1.5">
            @foreach($task->boardLabels as $label)
                <x-status-icon :name="$label->name" :color="$label->color" data-board-label-id="{{ $label->id }}" />
            @endforeach
            @if((int) $column->board_id !== (int) config('mangie.product_backlog_board_id', 1))<x-status-icon :name="$column->name" />@endif
            @if($task->priority)<x-status-icon :name="$task->priority" />@endif
            @if($task->due_at)<x-status-icon :name="$task->due_complete ? 'Due complete' : $task->due_at->format('M j')" />@endif
            @php($checklistItems = $task->checklists->flatMap(fn ($checklist) => $checklist->items))
            @if($checklistItems->isNotEmpty())<x-status-icon name="{{ $checklistItems->where('is_complete', true)->count() }}/{{ $checklistItems->count() }} checklist" />@endif
        </div>
        @if($task->users->isNotEmpty())
            <div class="flex justify-end -space-x-1.5" aria-label="Assigned users">
                @foreach($task->users->take(4) as $assignee)<x-user-avatar :user="$assignee" size="xs" ring title="{{ $assignee->name }}" />@endforeach
                @if($task->users->count() > 4)<span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-[9px] font-bold text-gray-600">+{{ $task->users->count() - 4 }}</span>@endif
            </div>
        @endif
    </div>
</div>
