<?php

namespace App\Events\Realtime;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

abstract class RealtimeMutation implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly array $payload,
        array $channelNames
    ) {
        // Never publish private board data to a project-wide channel. Resolve
        // recipients now, then send content-free invalidations; the HTTP read
        // checks membership again (including after a participant is removed).
        $boardIds = collect($channelNames)->filter(fn ($name) => str_starts_with($name, 'boards.'))
            ->map(fn ($name) => (int) substr($name, 7));
        if (($payload['entity_type'] ?? null) === 'board') {
            $boardIds->push((int) $payload['entity']['id']);
        }
        $userIds = DB::table('board_user')->whereIn('board_id', $boardIds)
            ->pluck('user_id');
        $this->channelNames = collect($channelNames)->filter(fn ($name) => str_starts_with($name, 'users.'))
            ->merge($userIds->map(fn ($id) => "users.{$id}"))->unique()->values()->all();
    }

    protected readonly array $channelNames;

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
        return [
            'event_id' => $this->payload['event_id'] ?? null,
            'entity_type' => $this->payload['entity_type'] ?? null,
            'entity' => ['id' => $this->payload['entity']['id']],
            'invalidate' => true,
        ];
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
