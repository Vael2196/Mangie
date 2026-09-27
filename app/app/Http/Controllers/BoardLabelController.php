<?php

namespace App\Http\Controllers;

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\BoardLabel;
use App\Models\LabelLibrary;
use App\Models\Task;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class BoardLabelController extends Controller
{
    public function store(
        Request $request,
        Board $board
    ): JsonResponse {
        Gate::authorize('update', $board);

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('board_labels', 'name')
                    ->where('board_id', $board->id),
            ],
            'color' => [
                'required',
                'string',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],
        ]);

        $label = DB::transaction(function () use ($board, $validated) {
            $position = (int) $board->labels()->max('position') + 1;

            $label = $board->labels()->create([
                ...$validated,
                'color' => strtolower($validated['color']),
                'position' => $position,
            ]);

            $this->touchBoard($board);

            return $label;
        }, 3);

        $this->broadcastChange($request, $board);

        return response()->json([
            'success' => true,
            'label' => $this->labelData($label),
        ], 201);
    }

    public function destroy(
        Request $request,
        BoardLabel $boardLabel
    ): JsonResponse {
        $boardLabel->load('board');
        Gate::authorize('update', $boardLabel->board);

        $board = $boardLabel->board;
        $taskIds = $boardLabel->tasks()->pluck('tasks.id');

        DB::transaction(function () use (
            $boardLabel,
            $board,
            $taskIds
        ) {
            $position = (int) $boardLabel->position;
            $boardLabel->delete();

            BoardLabel::query()
                ->where('board_id', $board->id)
                ->where('position', '>', $position)
                ->decrement('position');

            Task::query()
                ->whereIn('id', $taskIds)
                ->get()
                ->each(function (Task $task) {
                    $task->load('boardLabels');
                    $task->update([
                        'labels' => $task->boardLabels->first()?->name,
                        'version' => (int) $task->version + 1,
                    ]);
                });

            $this->touchBoard($board);
        }, 3);

        $this->broadcastChange($request, $board);

        return response()->json([
            'success' => true,
            'deleted_label_id' => (int) $boardLabel->id,
            'affected_task_ids' => $taskIds
                ->map(fn ($id) => (int) $id)
                ->values(),
        ]);
    }

    public function import(
        Request $request,
        Board $board
    ): JsonResponse {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'source' => [
                'required',
                Rule::in(['standard', 'library']),
            ],
            'library_id' => [
                'nullable',
                'required_if:source,library',
                'integer',
                'exists:label_libraries,id',
            ],
        ]);

        if ($validated['source'] === 'standard') {
            $items = collect(config('mangie.standard_labels', []));
        } else {
            $library = LabelLibrary::with('items')->findOrFail(
                $validated['library_id']
            );

            abort_unless(
                (int) $library->user_id === (int) $request->user()->id,
                403
            );

            $items = $library->items;
        }

        DB::transaction(function () use ($board, $items) {
            $position = (int) $board->labels()->max('position');

            foreach ($items as $item) {
                $name = is_array($item) ? $item['name'] : $item->name;
                $color = is_array($item) ? $item['color'] : $item->color;

                if (
                    $board->labels()
                        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                        ->exists()
                ) {
                    continue;
                }

                $board->labels()->create([
                    'name' => $name,
                    'color' => strtolower($color),
                    'position' => ++$position,
                ]);
            }

            $this->touchBoard($board);
        }, 3);

        $this->broadcastChange($request, $board);

        return response()->json([
            'success' => true,
            'labels' => $board->labels()
                ->get()
                ->map(fn (BoardLabel $label) => $this->labelData($label)),
        ]);
    }

    private function touchBoard(Board $board): void
    {
        $board->refresh();
        $board->update([
            'version' => (int) $board->version + 1,
        ]);
    }

    private function broadcastChange(
        Request $request,
        Board $board
    ): void {
        $board->refresh();

        broadcast(new BoardUpdated(
            RealtimePayload::board(
                $board,
                $request->user(),
                ['labels_changed' => true]
            ),
            (int) $board->project_id,
            (int) $board->id
        ))->toOthers();
    }

    private function labelData(BoardLabel $label): array
    {
        return [
            'id' => (int) $label->id,
            'name' => $label->name,
            'color' => $label->color,
            'position' => (int) $label->position,
        ];
    }
}
