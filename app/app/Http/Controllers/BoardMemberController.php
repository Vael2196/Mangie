<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BoardMemberController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $query = trim(
            (string) ($validated['query'] ?? '')
        );

        $users = User::query()
            ->select(['id', 'name'])
            ->when(
                $query !== '',
                fn ($builder) =>
                    $builder->where(
                        'name',
                        'like',
                        "%{$query}%"
                    )
            )
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    public function store(
        Request $request,
        Board $board
    ): JsonResponse {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $user = User::findOrFail(
            $validated['user_id']
        );

        $alreadyAdded = $board
            ->users()
            ->whereKey($user->id)
            ->exists();

        if ($alreadyAdded) {
            return response()->json([
                'success' => false,
                'message' =>
                    'User is already added to this board.',
            ], 409);
        }

        $board->users()->syncWithoutDetaching([
            $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User added successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ], 201);
    }
}
