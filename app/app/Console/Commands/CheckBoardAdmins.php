<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckBoardAdmins extends Command
{
    protected $signature = 'mangie:check-board-admins';

    protected $description = 'Read-only preflight for the board roles migration';

    public function handle(): int
    {
        if (! Schema::hasTable('boards')) {
            $this->info('Fresh database: the roles migration can proceed.');

            return self::SUCCESS;
        }
        $rolesInstalled = Schema::hasColumn('board_user', 'role');
        $query = DB::table('boards');
        if ($rolesInstalled) {
            $query->whereNotExists(fn ($q) => $q->selectRaw('1')->from('board_user')
                ->join('users', 'users.id', '=', 'board_user.user_id')
                ->whereColumn('board_user.board_id', 'boards.id')->where('board_user.role', 'admin')
                ->whereNotNull('users.email_verified_at'));
        } else {
            $query->leftJoin('users', 'users.id', '=', 'boards.user_id')->whereNull('users.email_verified_at');
        }
        $boards = $query->get(['boards.id', 'boards.name']);
        if ($boards->isEmpty()) {
            $this->info('Board admin checks passed.');

            return self::SUCCESS;
        }
        $this->table(['Board ID', 'Board'], $boards->map(fn ($board) => (array) $board)->all());
        $this->error($rolesInstalled
            ? 'These boards need a verified admin. Nothing was changed.'
            : 'These board creators must sign in and verify their email before the roles migration. Nothing was changed.');

        return self::FAILURE;
    }
}
