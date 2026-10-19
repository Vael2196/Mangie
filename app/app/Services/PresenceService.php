<?php

namespace App\Services;

use App\Models\User;

class PresenceService
{
    public function forUser(User $user, bool $self = false): array
    {
        if ($user->invisible) {
            return ['status' => 'offline', 'label' => $self ? 'Invisible' : 'Offline', 'last_seen_at' => null];
        }
        $online = $user->last_active_at && $user->last_active_at->gt(now()->subHour());

        return [
            'status' => $online ? 'online' : 'offline',
            'label' => $online ? 'Online' : ($user->last_active_at
                ? 'Last seen '.$user->last_active_at->diffForHumans()
                : 'Offline'),
            'last_seen_at' => $online ? null : $user->last_active_at?->toISOString(),
        ];
    }
}
