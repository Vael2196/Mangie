<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;

class ColumnColourChanged extends RealtimeMutation
{
    public function __construct(array $payload, int $boardId)
    {
        parent::__construct($payload, ["boards.{$boardId}"]);
    }

    public function broadcastAs(): string
    {
        return 'column.colour-changed';
    }
}
