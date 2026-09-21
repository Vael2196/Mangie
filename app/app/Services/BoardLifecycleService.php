<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BoardLifecycleService
{
    public function endIfExpired(Board $board): Board
    {
        if (
            !$board->completed
            && $board->end_date
            && now()->startOfDay()->gt(
                Carbon::parse($board->end_date)->endOfDay()
            )
        ) {
            $board->update([
                'completed' => true,
                'status' => false,
                'date_ended' => $board->end_date,
                'version' => DB::raw('version + 1'),
            ]);
        }

        return $board->refresh();
    }

    public function start(
        Board $board,
        string $endDate,
        string $sprintGoal
    ): Board {
        return DB::transaction(function () use (
            $board,
            $endDate,
            $sprintGoal
        ) {
            $board = Board::query()
                ->whereKey($board->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($board->completed) {
                throw ValidationException::withMessages([
                    'board' => 'A completed sprint cannot be restarted.',
                ]);
            }

            $anotherSprintIsActive = Board::query()
                ->where('project_id', $board->project_id)
                ->where('id', '<>', $board->id)
                ->where('status', true)
                ->where('completed', false)
                ->exists();

            if ($anotherSprintIsActive) {
                throw ValidationException::withMessages([
                    'board' => 'Another sprint is already active.',
                ]);
            }

            $start = now()->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();

            $totalStoryPoints = (int) Task::query()
                ->whereHas(
                    'column',
                    fn ($query) =>
                        $query->where('board_id', $board->id)
                )
                ->sum('story_points');

            $board->update([
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'duration' => $start->diffInDays($end),
                'sprint_goal' => $sprintGoal,
                'status' => true,
                'total_story_points' => $totalStoryPoints,
                'version' => DB::raw('version + 1'),
            ]);

            return $board->refresh();
        }, 3);
    }

    public function complete(Board $board): array
    {
        return DB::transaction(function () use ($board) {
            $board = Board::query()
                ->whereKey($board->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($board->completed) {
                return [
                    'board' => $board,
                    'moved_tasks' => collect(),
                ];
            }

            $backlogBoardId = (int) config(
                'mangie.product_backlog_board_id',
                1
            );

            if ((int) $board->id === $backlogBoardId) {
                throw ValidationException::withMessages([
                    'board' => 'The product backlog cannot be completed as a sprint.',
                ]);
            }

            $backlogColumn = Column::query()
                ->where('board_id', $backlogBoardId)
                ->orderBy('position')
                ->lockForUpdate()
                ->firstOrFail();

            $nextBacklogPosition = (int) Task::query()
                ->where('column_id', $backlogColumn->id)
                ->max('position');

            $tasksToReturn = Task::query()
                ->whereHas(
                    'column',
                    function ($query) use ($board) {
                        $query
                            ->where('board_id', $board->id)
                            ->whereRaw('UPPER(TRIM(name)) <> ?', ['DONE']);
                    }
                )
                ->orderBy('column_id')
                ->orderBy('position')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($tasksToReturn as $task) {
                $nextBacklogPosition++;

                $task->update([
                    'column_id' => $backlogColumn->id,
                    'position' => $nextBacklogPosition,
                    'completed_at' => null,
                    'version' => DB::raw('version + 1'),
                ]);
            }

            $board->update([
                'completed' => true,
                'status' => false,
                'date_ended' => now()->toDateString(),
                'version' => DB::raw('version + 1'),
            ]);

            return [
                'board' => $board->refresh(),
                'moved_tasks' => $tasksToReturn
                    ->map(
                        fn (Task $task) => $task
                            ->refresh()
                            ->load('column.board', 'users')
                    )
                    ->values(),
            ];
        }, 3);
    }
}
