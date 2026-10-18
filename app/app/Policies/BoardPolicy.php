<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;

class BoardPolicy
{
    public function view(
        User $user,
        Board $board
    ): bool {
        return $user->hasVerifiedEmail() && $board->roleFor($user) !== null;
    }


    public function update(
        User $user,
        Board $board
    ): bool {
        return !$board->completed && $this->invite($user, $board);
    }

    public function invite(User $user, Board $board): bool
    {
        return $user->hasVerifiedEmail()
            && in_array($board->roleFor($user), ['admin', 'member'], true);
    }

    public function manageMembers(User $user, Board $board): bool
    {
        return $user->hasVerifiedEmail() && $board->roleFor($user) === 'admin';
    }

    public function delete(
        User $user,
        Board $board
    ): bool {
        return (int) $board->id
                !== (int) config(
                    'mangie.product_backlog_board_id',
                    1
                )
            && $this->manageMembers($user, $board);
    }
}
