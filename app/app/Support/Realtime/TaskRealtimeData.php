<?php

namespace App\Support\Realtime;

use App\Models\Task;

final class TaskRealtimeData
{
    public static function from(Task $task): array
    {
        $task->loadMissing('column.board', 'users');

        return [
            'id' => (int) $task->id,
            'title' => $task->title,
            'description' => $task->description,
            'column_id' => (int) $task->column_id,
            'column_name' => $task->column->name,
            'board_id' => (int) $task->column->board_id,
            'project_id' => (int) $task->column->board->project_id,
            'position' => (int) $task->position,
            'labels' => $task->labels,
            'priority' => $task->priority,
            'story_points' => (int) $task->story_points,
            'time_log' => (int) $task->time_log,
            'completed_at' => $task->completed_at?->toISOString(),
            'assignee_ids' => $task->users
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'version' => (int) $task->version,
            'updated_at' => $task->updated_at?->toISOString(),
        ];
    }
}
