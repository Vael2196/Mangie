<?php

namespace App\Services;

use App\Jobs\SendTaskWatchEmail;
use App\Models\Task;
use App\Models\TaskWatch;
use Illuminate\Support\Facades\DB;

class TaskWatchService
{
    public function record(Task $task): void
    {
        // Called inside the mutation transaction, after all of its detail writes.
        TaskWatch::with('user')->where('task_id', $task->id)
            ->when(auth()->id(), fn ($q, $id) => $q->where('user_id', '<>', $id))
            ->lockForUpdate()->get()->each(function (TaskWatch $watch) {
                if (! $watch->user || $watch->user->notification_frequency === 'never') {
                    return;
                }
                $watch->update([
                    'pending_since' => $watch->pending_since ?? now(),
                    'actor_id' => auth()->id(),
                    'change_count' => $watch->change_count + 1,
                ]);
                if ($watch->user->notification_frequency === 'instantly') {
                    DB::afterCommit(fn () => SendTaskWatchEmail::dispatch($watch->id));
                }
            });
    }
}
