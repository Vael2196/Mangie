<?php

namespace App\Support\Realtime;

use App\Models\Column;

final class ColumnRealtimeData
{
    public static function from(Column $column): array
    {
        $column->loadMissing('board');
        $column->loadCount('tasks');

        return [
            'id' => (int) $column->id,
            'board_id' => (int) $column->board_id,
            'project_id' => (int) $column->board->project_id,
            'name' => $column->name,
            'position' => (int) $column->position,
            'color' => $column->color ?? 'gray',
            'tasks_count' => (int) $column->tasks_count,
            'version' => (int) $column->version,
            'updated_at' => $column->updated_at?->toISOString(),
        ];
    }
}
