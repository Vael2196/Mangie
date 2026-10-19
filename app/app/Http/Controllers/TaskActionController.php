<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\TaskWatch;
use App\Services\TaskActionService;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskActionController extends Controller
{
    public function show(Request $request, Task $task)
    {
        $task->load('column.board.columns', 'users');
        Gate::authorize('view', $task->column->board);

        return response()->json([
            'task' => ['id' => $task->id, 'title' => $task->title, 'version' => (int) $task->version,
                'column_id' => (int) $task->column_id, 'completed' => (bool) $task->completed_at, 'color' => $task->color],
            'can_edit' => Gate::allows('update', $task->column->board),
            'joined' => $task->users->contains('id', $request->user()->id),
            'watching' => TaskWatch::where('task_id', $task->id)->where('user_id', $request->user()->id)->exists(),
            'columns' => $task->column->board->columns->sortBy('position')->values()->map(fn ($column) => [
                'id' => $column->id, 'name' => $column->name,
            ]),
        ])->header('Cache-Control', 'private, no-store');
    }

    public function update(Request $request, Task $task, TaskActionService $actions)
    {
        Gate::authorize('update', $task->column->board);
        $data = $request->validate([
            'expected_version' => ['required', 'integer', 'min:1'],
            'completed' => ['sometimes', 'boolean'],
            'joined' => ['sometimes', 'boolean'],
            'color' => ['sometimes', 'nullable', Rule::in(array_keys(config('mangie.task_colors')))],
        ]);
        $task = $actions->change($task, $data);
        $event = new TaskUpdated(RealtimePayload::task($task, $request->user()), [(int) $task->column->board_id]);
        broadcast($event)->toOthers();

        return response()->json($event->response());
    }

    public function copy(Request $request, Task $task, TaskActionService $actions)
    {
        Gate::authorize('update', $task->column->board);
        $data = $request->validate(['expected_version' => ['required', 'integer', 'min:1']]);
        $copy = $actions->copy($task, $data['expected_version']);
        $meta = ['column_id' => (int) $copy->column_id, 'column_count' => $copy->column->tasks()->count()];
        if ((int) $copy->column->board_id === (int) config('mangie.product_backlog_board_id', 1)) {
            $meta['backlog_issue_count'] = Task::whereHas('column', fn ($q) => $q->where('board_id', $copy->column->board_id))->count();
        }
        $event = new TaskCreated(RealtimePayload::task($copy, $request->user(), $meta), (int) $copy->column->board_id);
        broadcast($event)->toOthers();

        return response()->json($event->response(), 201);
    }

    public function watch(Request $request, Task $task)
    {
        Gate::authorize('view', $task->column->board);
        $data = $request->validate(['watching' => ['required', 'boolean']]);
        if ($data['watching']) {
            TaskWatch::firstOrCreate(['task_id' => $task->id, 'user_id' => $request->user()->id]);
        } else {
            TaskWatch::where('task_id', $task->id)->where('user_id', $request->user()->id)->delete();
        }

        return response()->json(['success' => true, 'watching' => $data['watching']]);
    }
}
