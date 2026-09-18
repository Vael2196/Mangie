<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskMoved implements
    ShouldBroadcast,
    ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;


    public function __construct(
        public array $payload
    ) {
    }


    public function broadcastOn(): array
    {
        $boardIds = collect([
            $this->payload[
                'source_board_id'
            ],

            $this->payload[
                'target_board_id'
            ],
        ])
            ->unique();


        return $boardIds
            ->map(
                fn ($boardId) =>
                    new PrivateChannel(
                        'boards.' . $boardId
                    )
            )
            ->values()
            ->all();
    }


    public function broadcastAs(): string
    {
        return 'task.moved';
    }


    public function broadcastWith(): array
    {
        return $this->payload;
    }
}