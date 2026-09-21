<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskMoveService
{
    public function move(
        Task $task,
        Column $targetColumn,
        int $targetPosition
    ): array {
        return DB::transaction(function () use (
            $task,
            $targetColumn,
            $targetPosition
        ) {
            $task = Task::query()
                ->whereKey($task->id)
                ->lockForUpdate()
                ->firstOrFail();

            $sourceColumnId = (int) $task->column_id;
            $targetColumnId = (int) $targetColumn->id;

            $columnIds = collect([
                $sourceColumnId,
                $targetColumnId,
            ])
                ->unique()
                ->sort()
                ->values();


            $columns = Column::query()
                ->whereIn('id', $columnIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');


            $sourceColumn =
                $columns->get($sourceColumnId);

            $targetColumn =
                $columns->get($targetColumnId);


            if (!$sourceColumn || !$targetColumn) {
                throw ValidationException::withMessages([
                    'target_column_id' =>
                        'The source or target column no longer exists.',
                ]);
            }


            $targetBoard = Board::findOrFail(
                $targetColumn->board_id
            );


            if ($targetBoard->completed) {
                throw ValidationException::withMessages([
                    'target_column_id' =>
                        'Tasks cannot be moved into a completed sprint.',
                ]);
            }

            $affectedTasks = Task::query()
                ->whereIn(
                    'column_id',
                    $columnIds
                )
                ->orderBy('column_id')
                ->orderBy('position')
                ->orderBy('id')
                ->lockForUpdate()
                ->get([
                    'id',
                    'column_id',
                    'position',
                ]);


            if (
                $sourceColumnId
                === $targetColumnId
            ) {
                $ids = $affectedTasks
                    ->where(
                        'column_id',
                        $sourceColumnId
                    )
                    ->pluck('id')
                    ->reject(
                        fn ($id) =>
                            (int) $id
                            === (int) $task->id
                    )
                    ->values();


                $insertIndex =
                    $this->clampInsertIndex(
                        $targetPosition,
                        $ids->count()
                    );


                $ids->splice(
                    $insertIndex,
                    0,
                    [$task->id]
                );


                $this->writePositions(
                    $ids,
                    $sourceColumnId
                );

            } else {

                $sourceIds = $affectedTasks
                    ->where(
                        'column_id',
                        $sourceColumnId
                    )
                    ->pluck('id')
                    ->reject(
                        fn ($id) =>
                            (int) $id
                            === (int) $task->id
                    )
                    ->values();


                $targetIds = $affectedTasks
                    ->where(
                        'column_id',
                        $targetColumnId
                    )
                    ->pluck('id')
                    ->reject(
                        fn ($id) =>
                            (int) $id
                            === (int) $task->id
                    )
                    ->values();


                $insertIndex =
                    $this->clampInsertIndex(
                        $targetPosition,
                        $targetIds->count()
                    );


                $targetIds->splice(
                    $insertIndex,
                    0,
                    [$task->id]
                );


                $this->writePositions(
                    $sourceIds,
                    $sourceColumnId
                );


                $this->writePositions(
                    $targetIds,
                    $targetColumnId
                );
            }

            Task::whereKey($task->id)
                ->update([
                    'completed_at' =>
                        strcasecmp(
                            trim($targetColumn->name),
                            'DONE'
                        ) === 0
                            ? now()
                            : null,
                    'version' => DB::raw('version + 1'),
                ]);


            $finalTask = Task::query()
                ->with('column.board', 'users')
                ->findOrFail($task->id);


            $sourceCount =
                Task::where(
                    'column_id',
                    $sourceColumnId
                )->count();


            $targetCount =
                Task::where(
                    'column_id',
                    $targetColumnId
                )->count();


            $backlogBoardId =
                (int) config(
                    'mangie.product_backlog_board_id',
                    1
                );


            $backlogIssueCount =
                Task::whereHas(
                    'column',
                    function ($query) use (
                        $backlogBoardId
                    ) {
                        $query->where(
                            'board_id',
                            $backlogBoardId
                        );
                    }
                )->count();


            return [
                'task_id' =>
                    (int) $finalTask->id,

                'source_column_id' =>
                    $sourceColumnId,

                'target_column_id' =>
                    $targetColumnId,

                'source_board_id' =>
                    (int) $sourceColumn->board_id,

                'target_board_id' =>
                    (int) $targetColumn->board_id,

                'target_column_name' =>
                    $targetColumn->name,

                'target_position' =>
                    (int) $finalTask->position,

                'source_column_count' =>
                    $sourceCount,

                'target_column_count' =>
                    $targetCount,

                'backlog_issue_count' =>
                    $backlogIssueCount,

                'updated_at' =>
                    $finalTask
                        ->updated_at
                        ?->toISOString(),

                'task' => $finalTask,
            ];
        }, 3);
    }


    private function clampInsertIndex(
        int $requestedPosition,
        int $currentCount
    ): int {
        return max(
            0,
            min(
                $requestedPosition - 1,
                $currentCount
            )
        );
    }


    private function writePositions(
        Collection $ids,
        int $columnId
    ): void {
        foreach (
            $ids->values()
            as $index => $taskId
        ) {
            Task::whereKey($taskId)
                ->update([
                    'column_id' => $columnId,
                    'position' => $index + 1,
                ]);
        }
    }
}
