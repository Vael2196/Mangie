<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;

class TaskMoved extends RealtimeMutation
{
    public function __construct(array $payload, array $boardIds)
    {
        parent::__construct(
            $payload,
            collect($boardIds)
                ->map(fn ($id) => "boards.{$id}")
                ->all()
        );
    }

    public function broadcastAs(): string
    {
        return 'task.moved';
    }
}
