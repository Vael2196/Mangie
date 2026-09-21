<?php

namespace App\Events\Realtime;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class RealtimeMutation implements
    ShouldBroadcast,
    ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly array $payload,
        private readonly array $channelNames
    ) {
    }

    abstract public function broadcastAs(): string;

    public function broadcastOn(): array
    {
        return collect($this->channelNames)
            ->unique()
            ->map(
                fn (string $name) => new PrivateChannel($name)
            )
            ->values()
            ->all();
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }

    public function response(array $extra = []): array
    {
        return [
            'success' => true,
            'event' => $this->broadcastAs(),
            'payload' => $this->payload,
            ...$extra,
        ];
    }
}
