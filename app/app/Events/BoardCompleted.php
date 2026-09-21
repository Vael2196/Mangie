<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;

class BoardCompleted extends RealtimeMutation
{
    public function __construct(
        array $payload,
        int $projectId,
        array $boardIds
    ) {
        parent::__construct(
            $payload,
            [
                "projects.{$projectId}",
                ...collect($boardIds)
                    ->map(fn ($id) => "boards.{$id}")
                    ->all(),
            ]
        );
    }

    public function broadcastAs(): string
    {
        return 'board.completed';
    }
}
