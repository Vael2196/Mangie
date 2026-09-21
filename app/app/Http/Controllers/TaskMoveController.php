<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
use App\Models\Column;
use App\Models\Task;
use App\Services\TaskMoveService;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskMoveController extends Controller
{
    public function __construct(
        private readonly TaskMoveService $taskMover
    ) {
    }

    public function update(
        Request $request,
        Task $task
    ): JsonResponse {
        $validated = $request->validate([
            'target_column_id' => [
                'required',
                'integer',
                'exists:columns,id',
            ],
            'target_position' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        $task->load('column.board');

        $targetColumn = Column::with('board')->findOrFail(
            $validated['target_column_id']
        );

        Gate::authorize('update', $task->column->board);
        Gate::authorize('update', $targetColumn->board);

        $result = $this->taskMover->move(
            $task,
            $targetColumn,
            $validated['target_position']
        );

        $event = $this->eventFor(
            $request,
            $result
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
            ...$event->payload['meta'],
        ]);
    }

    public function bulk(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'task_ids' => [
                'required',
                'array',
                'min:1',
            ],
            'task_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:tasks,id',
            ],
            'target_column_id' => [
                'required',
                'integer',
                'exists:columns,id',
            ],
        ]);

        $targetColumn = Column::with('board')->findOrFail(
            $validated['target_column_id']
        );

        Gate::authorize('update', $targetColumn->board);

        $tasks = Task::query()
            ->with('column.board')
            ->whereIn('id', $validated['task_ids'])
            ->get()
            ->keyBy('id');

        $results = [];

        foreach ($validated['task_ids'] as $taskId) {
            $task = $tasks->get((int) $taskId);

            Gate::authorize('update', $task->column->board);

            if (
                (int) $task->column_id
                === (int) $targetColumn->id
            ) {
                continue;
            }

            $result = $this->taskMover->move(
                $task,
                $targetColumn,
                PHP_INT_MAX
            );

            $event = $this->eventFor(
                $request,
                $result
            );

            broadcast($event)->toOthers();

            $results[] = [
                'event' => $event->broadcastAs(),
                'payload' => $event->payload,
            ];
        }

        return response()->json([
            'success' => true,
            'mutations' => $results,
        ]);
    }

    private function eventFor(
        Request $request,
        array $result
    ): TaskMoved {
        $task = $result['task'];
        unset($result['task']);

        return new TaskMoved(
            RealtimePayload::task(
                $task,
                $request->user(),
                $result
            ),
            [
                $result['source_board_id'],
                $result['target_board_id'],
            ]
        );
    }
}
