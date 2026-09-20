<?php

namespace App\Services;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskMutationService
{
    public function __construct(
        private readonly TaskMoveService $taskMover
    ) {
    }

    public function create(
        Column $column,
        string $title
    ): Task {
        return DB::transaction(function () use ($column, $title) {
            $column = Column::query()
                ->whereKey($column->id)
                ->lockForUpdate()
                ->firstOrFail();

            $position = (int) Task::query()
                ->where('column_id', $column->id)
                ->max('position');

            return Task::create([
                'title' => $title,
                'column_id' => $column->id,
                'position' => $position + 1,
            ]);
        }, 3);
    }

    public function update(
        Task $task,
        Column $targetColumn,
        array $attributes,
        ?int $assigneeId
    ): array {
        return DB::transaction(function () use (
            $task,
            $targetColumn,
            $attributes,
            $assigneeId
        ) {
            $task = Task::query()
                ->whereKey($task->id)
                ->lockForUpdate()
                ->firstOrFail();

            $moveResult = null;

            if (
                (int) $task->column_id
                !== (int) $targetColumn->id
            ) {
                $moveResult = $this->taskMover->move(
                    $task,
                    $targetColumn,
                    PHP_INT_MAX
                );
            }

            $task->refresh();

            $task->update($attributes);

            $task->users()->sync(
                $assigneeId
                    ? [$assigneeId]
                    : []
            );

            return [
                'task' => $task
                    ->refresh()
                    ->load('users'),
                'move' => $moveResult,
            ];
        }, 3);
    }
}
