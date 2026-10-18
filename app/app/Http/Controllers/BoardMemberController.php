<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\User;
use App\Notifications\BoardInvitationNotification;
use App\Services\BoardMembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BoardMemberController extends Controller
{
    public function index(Board $board)
    {
        Gate::authorize('view', $board);
        $board->load('users');
        $invitations = Gate::allows('invite', $board)
            ? $board->invitations()->whereNull('revoked_at')->whereNull('accepted_at')
                ->where('expires_at', '>', now())->latest()->get()
            : collect();

        return view('boards.members', compact('board', 'invitations'));
    }

    public function search(Request $request, Board $board)
    {
        Gate::authorize('invite', $board);
        $validated = $request->validate(['query' => ['required', 'string', 'min:2', 'max:255']]);
        $query = trim($validated['query']);
        $users = User::query()->where(function ($q) use ($query) {
            // Do not publish a searchable directory of email addresses.
            $q->where('name', 'like', '%'.addcslashes($query, '%_\\').'%')
                ->orWhere('email', strtolower($query));
        })->orderBy('name')->limit(8)->get();

        return response()->json(['success' => true, 'users' => $users->map(fn ($user) => [
            'id' => $user->id, 'name' => $user->name, 'avatar_url' => $user->avatar_url,
            'member' => $board->roleFor($user) !== null,
        ])]);
    }

    public function store(Request $request, Board $board)
    {
        Gate::authorize('invite', $board);
        $validated = $request->validate([
            'email' => ['required_without:user_id', 'nullable', 'email', 'max:255'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'role' => ['required', Rule::in(['member', 'observer'])],
        ]);
        $email = strtolower(trim($validated['email'] ?? User::findOrFail($validated['user_id'])->email));
        if ($board->users()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            throw ValidationException::withMessages(['email' => 'This person is already a board participant.']);
        }
        [$invitation, $token] = DB::transaction(function () use ($request, $board, $email, $validated) {
            $board = Board::whereKey($board->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('invite', $board);
            $board->invitations()->where('email', $email)->whereNull('accepted_at')
                ->whereNull('revoked_at')->update(['revoked_at' => now()]);

            return $this->createInvitation($board, $request, $validated['role'], $email);
        }, 3);
        Notification::route('mail', $email)->notify(new BoardInvitationNotification(
            $board->name, $request->user()->name, $invitation->role,
            route('board-invitations.show', $token)
        ));

        return response()->json(['success' => true, 'message' => 'Invitation sent. They join only after accepting it.'], 201);
    }

    public function share(Request $request, Board $board)
    {
        Gate::authorize('invite', $board);
        $validated = $request->validate(['role' => ['required', Rule::in(['member', 'observer'])]]);
        [$invitation, $token] = DB::transaction(function () use ($board, $request, $validated) {
            $board = Board::whereKey($board->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('invite', $board);

            return $this->createInvitation($board, $request, $validated['role']);
        }, 3);

        return response()->json([
            'success' => true, 'url' => route('board-invitations.show', $token),
            'expires_at' => $invitation->expires_at->toISOString(),
        ], 201);
    }

    private function createInvitation(Board $board, Request $request, string $role, ?string $email = null): array
    {
        $token = Str::random(64);
        $invitation = $board->invitations()->create([
            'invited_by' => $request->user()->id, 'email' => $email, 'role' => $role,
            'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7),
        ]);

        return [$invitation, $token];
    }

    public function revoke(Board $board, BoardInvitation $invitation)
    {
        Gate::authorize('manageMembers', $board);
        abort_unless((int) $invitation->board_id === (int) $board->id, 404);
        $invitation->update(['revoked_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Board $board, User $user, BoardMembershipService $members)
    {
        Gate::authorize('manageMembers', $board);
        $validated = $request->validate(['role' => ['required', Rule::in(['admin', 'member', 'observer'])]]);
        $members->change($board, $request->user(), $user, $validated['role']);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, Board $board, User $user, BoardMembershipService $members)
    {
        Gate::authorize('manageMembers', $board);
        $members->change($board, $request->user(), $user, null);

        return response()->json(['success' => true]);
    }

    public function leave(Request $request, Board $board, BoardMembershipService $members)
    {
        $members->change($board, $request->user(), $request->user(), null);

        return response()->json(['success' => true, 'redirect' => route('home')]);
    }
}
