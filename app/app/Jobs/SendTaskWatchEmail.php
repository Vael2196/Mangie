<?php

namespace App\Jobs;

use App\Mail\TaskWatchMail;
use App\Models\TaskWatch;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class SendTaskWatchEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $uniqueFor = 3600;

    public function __construct(public int $watchId) {}

    public function uniqueId(): string
    {
        return (string) $this->watchId;
    }

    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function handle(): void
    {
        DB::transaction(function () {
            $watch = TaskWatch::with('user', 'task.column.board', 'actor')->lockForUpdate()->find($this->watchId);
            if (! $watch) {
                return;
            }
            // Membership and verification can change while a digest waits in the queue.
            if (! $watch->user?->hasVerifiedEmail() || ! $watch->task
                || ! Gate::forUser($watch->user)->allows('view', $watch->task->column->board)) {
                $watch->delete();

                return;
            }
            if ($watch->user->notification_frequency === 'never') {
                $watch->update(['pending_since' => null, 'change_count' => 0, 'actor_id' => null]);

                return;
            }
            if (! $watch->isDue()) {
                return;
            }
            Mail::to($watch->user)->send(new TaskWatchMail($watch));
            $watch->update([
                'pending_since' => null, 'change_count' => 0, 'actor_id' => null, 'last_notified_at' => now(),
            ]);
        }, 1);
    }
}
