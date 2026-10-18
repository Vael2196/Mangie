<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Models\User;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RealtimeEntityController extends Controller
{
    public function summary(Request $request)
    {
        return response()->json(['success' => true, 'meta' => $this->counts($request)])
            ->header('Cache-Control', 'no-store');
    }

    private function counts(Request $request): array
    {
        $meta = ['column_counts' => Column::whereHas('board', fn ($q) => $q->accessibleTo($request->user()))
            ->withCount('tasks')->get()->mapWithKeys(fn ($column) => [$column->id => $column->tasks_count])->all()];
        $backlog = Board::find(config('mangie.product_backlog_board_id', 1));
        if ($backlog && Gate::allows('view', $backlog)) {
            $meta['backlog_issue_count'] = Task::whereHas('column', fn ($q) => $q->where('board_id', $backlog->id))->count();
        }

        return $meta;
    }

    public function show(Request $request, string $type, int $id)
    {
        $model = match ($type) {
            'task' => Task::with('column.board', 'users', 'boardLabels', 'checklists.items')->findOrFail($id),
            'column' => Column::with('board')->findOrFail($id),
            'board' => Board::findOrFail($id),
            'user' => User::findOrFail($id),
            default => abort(404),
        };
        if ($type === 'user') {
            abort_unless($request->user()->is($model) || Board::accessibleTo($request->user())
                ->whereHas('users', fn ($q) => $q->whereKey($model->id))->exists(), 403);
        } else {
            $board = match ($type) {
                'board' => $model, 'column' => $model->board, 'task' => $model->column->board,
            };
            Gate::authorize('view', $board);
        }
        $meta = $this->counts($request);
        if ($type === 'board') {
            $meta['column_order'] = $model->columns()->orderBy('position')->pluck('id')->all();
        }
        $payload = RealtimePayload::$type($model, null, $meta);

        return response()->json(['success' => true, 'payload' => $payload])->header('Cache-Control', 'no-store');
    }
}
