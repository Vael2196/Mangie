<?php

namespace App\Events;

use App\Events\Realtime\RealtimeMutation;

class UserProfileUpdated extends RealtimeMutation
{
    public function __construct(
        array $payload,
        array $boardIds,
        array $projectIds
    ) {
        parent::__construct(
            $payload,
            [
                ...collect($boardIds)
                    ->map(fn ($id) => "boards.{$id}")
                    ->all(),
                ...collect($projectIds)
                    ->map(fn ($id) => "projects.{$id}")
                    ->all(),
            ]
        );
    }

    public function broadcastAs(): string
    {
        return 'user.profile-updated';
    }
}
