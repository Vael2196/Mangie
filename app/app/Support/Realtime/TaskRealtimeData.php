<?php

namespace App\Support\Realtime;

use App\Models\Task;

final class TaskRealtimeData
{
    public static function from(Task $task): array
    {
        $task->loadMissing(
            'column.board',
            'users',
            'boardLabels',
            'sections',
            'checklists.items'
        );

        $checklistItems = $task->checklists
            ->flatMap(fn ($checklist) => $checklist->items);

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
            'start_at' => $task->start_at?->toISOString(),
            'due_at' => $task->due_at?->toISOString(),
            'due_complete' => (bool) $task->due_complete,
            'label_ids' => $task->boardLabels
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all(),
            'labels_data' => $task->boardLabels
                ->map(fn ($label) => [
                    'id' => (int) $label->id,
                    'name' => $label->name,
                    'color' => $label->color,
                ])
                ->values()
                ->all(),
            'section_count' => $task->sections->count(),
            'checklist_total' => $checklistItems->count(),
            'checklist_complete' => $checklistItems
                ->where('is_complete', true)
                ->count(),
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
