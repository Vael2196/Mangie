<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;

class BoardCreated extends RealtimeMutation
{
    public function __construct(array $payload, int $projectId)
    {
        parent::__construct($payload, ["projects.{$projectId}"]);
    }

    public function broadcastAs(): string
    {
        return 'board.created';
    }
}
