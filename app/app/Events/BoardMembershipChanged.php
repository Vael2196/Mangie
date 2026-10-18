<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;
use Illuminate\Support\Str;

class BoardMembershipChanged extends RealtimeMutation
{
    public function __construct(int $boardId, array $userIds)
    {
        parent::__construct([
            'event_id' => (string) Str::uuid(), 'entity_type' => 'board',
            'entity' => ['id' => $boardId], 'meta' => [],
        ], array_map(fn ($id) => "users.{$id}", array_unique($userIds)));
    }

    public function broadcastAs(): string
    {
        return 'board.membership-changed';
    }
}
