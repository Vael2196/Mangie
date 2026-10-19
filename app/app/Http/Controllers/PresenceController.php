<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Services\PresenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PresenceController extends Controller
{
    public function board(Board $board, PresenceService $presence)
    {
        Gate::authorize('viewPresence', $board);

        return response()->json(['users' => $board->users->map(fn ($user) => [
            'id' => $user->id, ...$presence->forUser($user),
        ])])->header('Cache-Control', 'private, no-store');
    }

    public function self(Request $request, PresenceService $presence)
    {
        return response()->json($presence->forUser($request->user(), true))
            ->header('Cache-Control', 'private, no-store');
    }

    public function activity()
    {
        return response()->noContent();
    }
}
