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
        if (
            (int) $board->id
            === (int) config(
                'mangie.product_backlog_board_id',
                1
            )
        ) {
            return true;
        }

        if (
            (int) $board->user_id
            === (int) $user->id
        ) {
            return true;
        }

        return $board
            ->users()
            ->whereKey($user->id)
            ->exists();
    }


    public function update(
        User $user,
        Board $board
    ): bool {
        return !$board->completed
            && $this->view($user, $board);
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
            && (int) $board->user_id
                === (int) $user->id;
    }
}
