<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BacklogService
{
    /** Resolve a writable backlog without granting access or creating anything. */
    public function current(User $user): ?Board
    {
        if (! $user->hasVerifiedEmail()) {
            return null;
        }
        $id = DB::table('users')->where('id', $user->id)->value('backlog_board_id');
        $candidate = $id ? Board::find($id) : null;
        if ($candidate?->isBacklog() && Gate::forUser($user)->allows('update', $candidate)) {
            return $candidate;
        }
        $owned = Board::backlogs()->where('completed', false)->where('user_id', $user->id)
            ->whereHas('users', fn ($q) => $q->whereKey($user->id)->whereIn('board_user.role', ['admin', 'member']))
            ->orderBy('id')->first();
        if ($owned) {
            return $owned;
        }
        $legacy = Board::backlogs()->find((int) config('mangie.product_backlog_board_id', 1));

        return $legacy && Gate::forUser($user)->allows('update', $legacy) ? $legacy : null;
    }

    /** Provision one private backlog when the user has no writable backlog. */
    public function forUser(User $user): Board
    {
        abort_unless($user->hasVerifiedEmail(), 403);
        $backlog = DB::transaction(function () use ($user) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($lockedUser->hasVerifiedEmail(), 403);
            $backlog = $this->current($lockedUser);
            if (! $backlog) {
                $project = Project::orderBy('id')->first() ?? Project::create(['name' => 'Mangie']);
                $backlog = Board::create([
                    'name' => 'Product Backlog', 'project_id' => $project->id,
                    'user_id' => $user->id, 'is_backlog' => true,
                ]);
                foreach (config('mangie.standard_labels', []) as $index => $label) {
                    $backlog->labels()->create([...$label, 'position' => $index + 1]);
                }
            }
            if (! $backlog->columns()->exists()) {
                $backlog->columns()->create(['name' => 'Backlog', 'position' => 1, 'color' => 'gray']);
            }
            DB::table('users')->where('id', $user->id)->update(['backlog_board_id' => $backlog->id]);

            return $backlog;
        }, 3);
        $user->backlog_board_id = $backlog->id;
        $user->syncOriginalAttribute('backlog_board_id');

        return $backlog;
    }

    public function countsFor(?User $user): array
    {
        $backlog = $user ? $this->current($user) : null;
        if (! $backlog) {
            return [];
        }

        return [
            'backlog_board_id' => (int) $backlog->id,
            'backlog_issue_count' => Task::whereHas('column', fn ($q) => $q->where('board_id', $backlog->id))->count(),
        ];
    }

    /** A member completing a shared sprint must not take it into their private backlog. */
    public function forSprint(Board $sprint): ?Board
    {
        $creator = User::find($sprint->user_id);
        if (! $creator || ! $creator->hasVerifiedEmail()) {
            return null;
        }

        return (int) auth()->id() === (int) $creator->id
            ? $this->forUser($creator)
            : $this->current($creator);
    }
}
