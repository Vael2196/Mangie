<?php

namespace App\Services;

use App\Events\BoardMembershipChanged;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BoardMembershipService
{
    public function change(Board $board, User $actor, User $target, ?string $role): void
    {
        DB::transaction(function () use ($board, $actor, $target, $role) {
            $board = Board::whereKey($board->id)->lockForUpdate()->firstOrFail();
            $target = User::whereKey($target->id)->lockForUpdate()->firstOrFail();
            $leaving = $actor->is($target) && $role === null;
            Gate::forUser($actor)->authorize($leaving ? 'view' : 'manageMembers', $board);
            abort_unless($board->roleFor($target), 404);
            if ($role === 'admin' && ! $target->hasVerifiedEmail()) {
                throw ValidationException::withMessages(['role' => 'Administrators must have a verified email address.']);
            }
            if ($role !== 'admin') {
                $this->requireOtherAdmin($board, $target);
            }
            if ($role === null) {
                DB::table('board_membership_revocations')->updateOrInsert(
                    ['board_id' => $board->id, 'user_id' => $target->id],
                    ['invitation_id_cutoff' => (int) $board->invitations()->max('id')]
                );
                $board->users()->detach($target->id);
                DB::table('task_users')->where('user_id', $target->id)
                    ->whereIn('task_id', DB::table('tasks')->select('tasks.id')
                        ->join('columns', 'columns.id', '=', 'tasks.column_id')->where('columns.board_id', $board->id))
                    ->delete();
                // Old invitations/share links must not restore revoked access.
                $board->invitations()->whereNull('revoked_at')->where(function ($q) use ($target) {
                    $q->where('email', strtolower($target->email));
                    $q->orWhere('invited_by', $target->id);
                })->update(['revoked_at' => now()]);
            } else {
                $board->users()->updateExistingPivot($target->id, ['role' => $role]);
                if ($role === 'observer') {
                    $board->invitations()->where('invited_by', $target->id)->whereNull('revoked_at')
                        ->update(['revoked_at' => now()]);
                }
            }
            $board->increment('version');
            DB::afterCommit(fn () => broadcast(new BoardMembershipChanged(
                (int) $board->id,
                [...$board->users()->pluck('users.id')->all(), $target->id]
            )));
        }, 3);
    }

    public function requireOtherAdmin(Board $board, User $target): void
    {
        if ($board->roleFor($target) !== 'admin') {
            return;
        }
        if (! $board->users()->wherePivot('role', 'admin')->whereNotNull('email_verified_at')
            ->where('users.id', '<>', $target->id)->exists()) {
            throw ValidationException::withMessages([
                'role' => 'Every board needs at least one verified administrator. Promote another verified member first.',
            ]);
        }
    }

    public function guardAccountChange(User $user, callable $change): mixed
    {
        return DB::transaction(function () use ($user, $change) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $boards = Board::where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereHas('users', fn ($q) => $q->whereKey($user->id)->where('board_user.role', 'admin')))
                ->orderBy('boards.id')->lockForUpdate()->get();
            foreach ($boards as $board) {
                $this->requireOtherAdmin($board, $user);
            }

            return $change();
        }, 3);
    }

    public function deleteAccount(User $user): void
    {
        $this->guardAccountChange($user, function () use ($user) {
            // The old creator FK cascades on account deletion. Transfer that
            // historical reference rather than deleting other people's boards.
            foreach (Board::where('user_id', $user->id)->get() as $board) {
                $successor = $board->users()->wherePivot('role', 'admin')
                    ->whereNotNull('email_verified_at')->where('users.id', '<>', $user->id)->first();
                if (! $successor) {
                    throw ValidationException::withMessages(['role' => 'Promote another verified administrator before deleting your account.']);
                }
                $board->update(['user_id' => $successor->id]);
            }
            BoardInvitation::where('invited_by', $user->id)->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);
            // Logout rotates the remember token and saves the user. Do this
            // before deletion so the guard cannot re-insert a deleted model.
            if ((int) auth()->id() === (int) $user->id) {
                Auth::logout();
            }
            $user->delete();
        });
    }
}
