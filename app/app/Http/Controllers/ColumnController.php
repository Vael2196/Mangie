<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use App\Services\ColumnMutationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ColumnController extends Controller
{
    public function __construct(
        private readonly ColumnMutationService $columns
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'name' => trim(
                (string) $request->input('name')
            ),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'board_id' => [
                'required',
                'integer',
                'exists:boards,id',
            ],
        ]);

        $board = Board::findOrFail(
            $validated['board_id']
        );

        Gate::authorize('update', $board);

        $column = $this->columns->create(
            $board,
            $validated['name']
        );

        return response()->json([
            'success' => true,
            'column' => $column,
        ], 201);
    }

    public function updateName(
        Request $request,
        Column $column
    ): JsonResponse {
        $column->load('board');

        Gate::authorize('update', $column->board);

        $request->merge([
            'name' => trim(
                (string) $request->input('name')
            ),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $column = $this->columns->rename(
            $column,
            $validated['name']
        );

        return response()->json([
            'success' => true,
            'column' => [
                'id' => $column->id,
                'name' => $column->name,
            ],
        ]);
    }

    public function updateColor(
        Request $request,
        Column $column
    ): JsonResponse {
        $column->load('board');

        Gate::authorize('update', $column->board);

        $validated = $request->validate([
            'color' => [
                'required',
                'string',
                Rule::in([
                    'gray',
                    'blue',
                    'green',
                    'yellow',
                    'orange',
                    'red',
                    'purple',
                    'pink',
                ]),
            ],
        ]);

        $column = $this->columns->recolour(
            $column,
            $validated['color']
        );

        return response()->json([
            'success' => true,
            'column' => $column,
        ]);
    }

    public function copy(
        Column $column
    ): JsonResponse {
        $column->load('board');

        Gate::authorize('update', $column->board);

        $copiedColumn = $this->columns->copy($column);

        return response()->json([
            'success' => true,
            'column' => $copiedColumn,
        ], 201);
    }

    public function destroy(
        Column $column
    ): JsonResponse {
        $column->load('board');

        Gate::authorize('update', $column->board);

        $this->columns->delete($column);

        return response()->json([
            'success' => true,
        ]);
    }
}
