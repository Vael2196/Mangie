<?php

namespace App\Http\Controllers;

use App\Events\ColumnColourChanged;
use App\Events\ColumnCopied;
use App\Events\ColumnCreated;
use App\Events\ColumnDeleted;
use App\Events\ColumnRenamed;
use App\Events\ColumnsReordered;
use App\Models\Board;
use App\Models\Column;
use App\Services\ColumnMutationService;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ColumnController extends Controller
{
    public function reorder(Request $request, Board $board): JsonResponse
    {
        Gate::authorize('update', $board);
        $validated = $request->validate([
            'column_ids' => ['required', 'array', 'min:1', 'max:500'],
            'column_ids.*' => ['required', 'integer', 'distinct'],
            'expected_version' => ['required', 'integer', 'min:1'],
            'expected_order' => ['sometimes', 'array', 'min:1', 'max:500'],
            'expected_order.*' => ['required', 'integer', 'distinct'],
        ]);
        $board = DB::transaction(function () use ($board, $validated) {
            $board = Board::whereKey($board->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $board);
            $ids = array_map('intval', $validated['column_ids']);
            $columns = $board->columns()->lockForUpdate()->get();
            $currentOrder = $columns->sortBy('position')->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $orderMatches = isset($validated['expected_order'])
                ? $currentOrder === array_map('intval', $validated['expected_order'])
                : (int) $board->version === (int) $validated['expected_version'];
            abort_unless($orderMatches, 409,
                'The column order changed elsewhere. Refresh before reordering columns.');
            $actual = $columns->pluck('id')->map(fn ($id) => (int) $id)->all();
            sort($actual);
            $submitted = $ids;
            sort($submitted);
            if ($submitted !== $actual) {
                throw ValidationException::withMessages([
                    'column_ids' => 'Include every column from this board exactly once, and no columns from other boards.',
                ]);
            }
            foreach ($ids as $index => $id) {
                $column = $columns->firstWhere('id', $id);
                $column->update(['position' => $index + 1, 'version' => (int) $column->version + 1]);
            }
            $board->update(['version' => (int) $board->version + 1]);

            return $board;
        }, 3);
        $event = new ColumnsReordered(
            RealtimePayload::board($board, $request->user(), ['column_order' => $validated['column_ids']]),
            (int) $board->id
        );
        broadcast($event)->toOthers();

        return response()->json($event->response());
    }

    public function __construct(
        private readonly ColumnMutationService $columns
    ) {}

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

        $event = new ColumnCreated(
            RealtimePayload::column(
                $column,
                $request->user()
            ),
            (int) $board->id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
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

        $event = new ColumnRenamed(
            RealtimePayload::column(
                $column,
                $request->user()
            ),
            (int) $column->board_id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
            'column' => $column,
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

        $event = new ColumnColourChanged(
            RealtimePayload::column(
                $column,
                $request->user()
            ),
            (int) $column->board_id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
            'column' => $column,
        ]);
    }

    public function copy(
        Request $request,
        Column $column
    ): JsonResponse {
        $column->load('board');

        Gate::authorize('update', $column->board);

        $copiedColumn = $this->columns->copy($column);

        $event = new ColumnCopied(
            RealtimePayload::column(
                $copiedColumn,
                $request->user(),
                ['source_column_id' => (int) $column->id]
            ),
            (int) $copiedColumn->board_id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
            'column' => $copiedColumn,
        ], 201);
    }

    public function destroy(
        Request $request,
        Column $column
    ): JsonResponse {
        $column->load('board');

        Gate::authorize('update', $column->board);

        $boardId = (int) $column->board_id;
        $payload = RealtimePayload::deleted(
            'column',
            (int) $column->id,
            (int) $column->version + 1,
            $request->user(),
            [
                'board_id' => $boardId,
                'position' => (int) $column->position,
            ]
        );

        $this->columns->delete($column);

        $event = new ColumnDeleted($payload, $boardId);

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
        ]);
    }
}
