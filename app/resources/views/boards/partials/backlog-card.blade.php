@php
    $dropColumn = $board->columns->firstWhere('name', 'TO DO')
        ?? $board->columns->sortBy('position')->first();
@endphp
<div
    id="sprint-loading-board-{{ $board->id }}"
    class="mb-3 sprint-board-status-{{ $board->status }}"
    data-board-id="{{ $board->id }}"
>
    @if(!$board->completed)
        <x-sprint-loading-board-card
            :board="$board"
            :activeSprints="$activeSprints"
            data-task-dropzone="true"
            data-task-list
            data-column-id="{{ $dropColumn->id }}"
            data-board-id="{{ $board->id }}"
            data-reorder="false"
        >
            @foreach($board->columns as $column)
                @foreach($column->tasks as $task)
                    @include('boards.partials.task-card', ['task' => $task])
                @endforeach
            @endforeach
        </x-sprint-loading-board-card>
    @endif
</div>
