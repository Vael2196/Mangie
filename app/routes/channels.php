<?php

use App\Models\Board;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;

Broadcast::channel(
    'boards.{boardId}',
    function (
        User $user,
        int $boardId
    ) {
        $board =
            Board::find($boardId);

        if (!$board) {
            return false;
        }

        return Gate::forUser($user)
            ->allows(
                'view',
                $board
            );
    }
);

Broadcast::channel(
    'projects.{projectId}',
    function (User $user, int $projectId) {
        return Board::query()
            ->where('project_id', $projectId)
            ->accessibleTo($user)
            ->exists();
    }
);
