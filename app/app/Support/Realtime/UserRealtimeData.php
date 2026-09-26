<?php

namespace App\Support\Realtime;

use App\Models\User;

final class UserRealtimeData
{
    public static function from(User $user): array
    {
        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'avatar_url' => $user->avatar_url,
            'updated_at' => $user->updated_at?->toISOString(),
        ];
    }
}
