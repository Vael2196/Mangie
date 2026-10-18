<?php

namespace App\Http\Controllers;

use App\Events\BoardMembershipChanged;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardInvitationController extends Controller
{
    private function find(string $token): BoardInvitation
    {
        abort_unless(preg_match('/^[A-Za-z0-9]{64}$/', $token), 404);

        return BoardInvitation::where('token_hash', hash('sha256', $token))->firstOrFail();
    }

    public function show(Request $request, string $token)
    {
        $invitation = $this->find($token);
        $user = $request->user();
        if ($invitation->accepted_at && ! $invitation->revoked_at && $user
            && strtolower($user->email) === $invitation->email && $invitation->board->roleFor($user)) {
            return redirect()->route('boards.show', $invitation->board_id);
        }
        abort_unless($invitation->isAvailable(), 410, 'This invitation has expired or was revoked. Ask for a new invitation.');
        // Registration and email verification must return to the invitation.
        $request->session()->put('url.intended', route('board-invitations.show', $token));
        $matchesEmail = ! $invitation->email || ($user && strtolower($user->email) === $invitation->email);

        return response()->view('boards.invitation', compact('invitation', 'token', 'user', 'matchesEmail'))
            ->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function accept(Request $request, string $token)
    {
        $invitation = $this->find($token);
        $board = DB::transaction(function () use ($request, $invitation) {
            $board = Board::whereKey($invitation->board_id)->lockForUpdate()->firstOrFail();
            $invitation = BoardInvitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless($user->hasVerifiedEmail(), 403, 'Verify your email before joining.');
            abort_unless($invitation->isAvailable(), 410, 'This invitation has expired or was revoked.');
            abort_unless(! $invitation->email || strtolower($user->email) === $invitation->email,
                403, 'Sign in with the email address that received this invitation.');
            // Existing users never gain a higher role by accepting a link.
            if (! $board->roleFor($user)) {
                $revokedThrough = DB::table('board_membership_revocations')
                    ->where('board_id', $board->id)->where('user_id', $user->id)->value('invitation_id_cutoff');
                abort_if($revokedThrough !== null && (int) $invitation->id <= (int) $revokedThrough,
                    403, 'You left or were removed from this board. Ask for a new invitation.');
                $board->users()->attach($user->id, ['role' => $invitation->role]);
                DB::table('board_membership_revocations')->where('board_id', $board->id)->where('user_id', $user->id)->delete();
                $board->increment('version');
            }
            if ($invitation->email) {
                $invitation->update(['accepted_at' => now()]);
            }
            DB::afterCommit(fn () => broadcast(new BoardMembershipChanged(
                (int) $board->id, $board->users()->pluck('users.id')->all()
            )));

            return $board;
        }, 3);
        $request->session()->forget('url.intended');

        return redirect()->route('boards.show', $board);
    }
}
