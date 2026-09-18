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
        return $this->view(
            $user,
            $board
        );
    }
}