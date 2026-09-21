<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;

class BoardDeleted extends RealtimeMutation
{
    public function __construct(
        array $payload,
        int $projectId,
        int $boardId
    ) {
        parent::__construct($payload, [
            "projects.{$projectId}",
            "boards.{$boardId}",
        ]);
    }

    public function broadcastAs(): string
    {
        return 'board.deleted';
    }
}
