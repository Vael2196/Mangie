<?php

namespace App\Support\Realtime;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Str;

final class RealtimePayload
{
    public static function task(
        Task $task,
        ?User $actor,
        array $meta = []
    ): array {
        return self::make(
            'task',
            TaskRealtimeData::from($task),
            $actor,
            $meta
        );
    }

    public static function column(
        Column $column,
        ?User $actor,
        array $meta = []
    ): array {
        return self::make(
            'column',
            ColumnRealtimeData::from($column),
            $actor,
            $meta
        );
    }

    public static function board(
        Board $board,
        ?User $actor,
        array $meta = []
    ): array {
        return self::make(
            'board',
            BoardRealtimeData::from($board),
            $actor,
            $meta
        );
    }

    public static function deleted(
        string $type,
        int $id,
        int $version,
        ?User $actor,
        array $meta = []
    ): array {
        return [
            'event_id' => (string) Str::uuid(),
            'occurred_at' => now()->toISOString(),
            'entity_type' => $type,
            'entity' => [
                'id' => $id,
                'version' => $version,
            ],
            'actor' => self::actor($actor),
            'meta' => $meta,
        ];
    }

    private static function make(
        string $type,
        array $entity,
        ?User $actor,
        array $meta
    ): array {
        return [
            'event_id' => (string) Str::uuid(),
            'occurred_at' => now()->toISOString(),
            'entity_type' => $type,
            'entity' => $entity,
            'actor' => self::actor($actor),
            'meta' => $meta,
        ];
    }

    private static function actor(?User $actor): ?array
    {
        if (!$actor) {
            return null;
        }

        return [
            'id' => (int) $actor->id,
            'name' => $actor->name,
        ];
    }
}
