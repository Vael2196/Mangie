<?php

namespace App\Console\Commands;

use App\Jobs\SendTaskWatchEmail;
use App\Models\TaskWatch;
use Illuminate\Console\Command;

class SendWatchNotifications extends Command
{
    protected $signature = 'mangie:send-watch-notifications';

    protected $description = 'Queue due emails for watched task updates';

    public function handle(): int
    {
        TaskWatch::with('user')->whereNotNull('pending_since')->chunkById(200, function ($watches) {
            foreach ($watches as $watch) {
                if ($watch->isDue()) {
                    SendTaskWatchEmail::dispatch($watch->id);
                }
            }
        });

        return self::SUCCESS;
    }
}
