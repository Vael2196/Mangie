<?php

namespace App\Services;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

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
        ?int $assigneeId,
        ?int $expectedVersion = null
    ): array {
        return DB::transaction(function () use (
            $task,
            $targetColumn,
            $attributes,
            $assigneeId,
            $expectedVersion
        ) {
            $task = Task::query()
                ->whereKey($task->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $expectedVersion !== null
                && (int) $task->version !== $expectedVersion
            ) {
                throw new ConflictHttpException(
                    'This task changed in another browser. Reopen it and try again.'
                );
            }

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

            $task->update([
                ...$attributes,
                'version' => DB::raw('version + 1'),
            ]);

            $task->users()->sync(
                $assigneeId
                    ? [$assigneeId]
                    : []
            );

            return [
                'task' => $task
                    ->refresh()
                    ->load('column.board', 'users'),
                'move' => $moveResult,
            ];
        }, 3);
    }

    public function delete(Task $task): array
    {
        return DB::transaction(function () use ($task) {
            $task = Task::query()
                ->whereKey($task->id)
                ->lockForUpdate()
                ->firstOrFail();

            $columnId = (int) $task->column_id;
            $position = (int) $task->position;
            $version = (int) $task->version + 1;

            $task->delete();

            Task::query()
                ->where('column_id', $columnId)
                ->where('position', '>', $position)
                ->decrement('position');

            return [
                'task_id' => (int) $task->id,
                'column_id' => $columnId,
                'version' => $version,
                'column_count' => Task::query()
                    ->where('column_id', $columnId)
                    ->count(),
            ];
        }, 3);
    }
}
