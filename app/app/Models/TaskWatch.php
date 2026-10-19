<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskWatch extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['pending_since' => 'datetime', 'last_notified_at' => 'datetime', 'change_count' => 'integer'];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function isDue(): bool
    {
        if (! $this->pending_since || ! $this->user || $this->user->notification_frequency === 'never') {
            return false;
        }
        $minutes = config('notifications.frequencies.'.$this->user->notification_frequency.'.minutes', 180);

        return $this->pending_since->lte(now()->subMinutes($minutes));
    }
}
