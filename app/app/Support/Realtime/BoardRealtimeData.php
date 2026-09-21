<?php

namespace App\Support\Realtime;

use App\Models\Board;

final class BoardRealtimeData
{
    public static function from(Board $board): array
    {
        return [
            'id' => (int) $board->id,
            'project_id' => (int) $board->project_id,
            'name' => $board->name,
            'status' => (bool) $board->status,
            'completed' => (bool) $board->completed,
            'start_date' => $board->start_date?->toDateString(),
            'end_date' => $board->end_date?->toDateString(),
            'date_ended' => $board->date_ended?->toDateString(),
            'duration' => (int) $board->duration,
            'sprint_goal' => $board->sprint_goal,
            'total_story_points' => (int) $board->total_story_points,
            'version' => (int) $board->version,
            'updated_at' => $board->updated_at?->toISOString(),
        ];
    }
}
