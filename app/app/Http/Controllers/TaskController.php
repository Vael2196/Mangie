<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
use App\Models\Column;
use App\Models\Task;
use App\Services\TaskMutationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskMutationService $tasks
    ) {
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'title' => trim(
                (string) $request->input('title')
            ),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'column_id' => [
                'required',
                'integer',
                'exists:columns,id',
            ],
        ]);

        $column = Column::with('board')->findOrFail(
            $validated['column_id']
        );

        Gate::authorize('update', $column->board);

        $task = $this->tasks->create(
            $column,
            $validated['title']
        );

        return response()->json([
            'success' => true,
            'task' => $task,
        ], 201);
    }

    public function update(
        Request $request,
        Task $task
    ): JsonResponse {
        $request->merge([
            'title' => trim(
                (string) $request->input('title')
            ),
        ]);

        $validated = $request->validate([
            'column_id' => [
                'required',
                'integer',
                'exists:columns,id',
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignee' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'labels' => [
                'nullable',
                'string',
                Rule::in([
                    'API',
                    'Backend',
                    'Frontend',
                    'UI/UX',
                    'Database',
                ]),
            ],
            'priority' => [
                'nullable',
                'string',
                Rule::in([
                    'Low',
                    'Medium',
                    'High',
                ]),
            ],
            'storyPoint' => [
                'required',
                'integer',
                'min:0',
            ],
            'timeLog' => [
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $task->load('column.board');

        $targetColumn = Column::with('board')->findOrFail(
            $validated['column_id']
        );

        Gate::authorize('update', $task->column->board);
        Gate::authorize('update', $targetColumn->board);

        $result = $this->tasks->update(
            $task,
            $targetColumn,
            [
                'title' => $validated['title'],
                'description' =>
                    $validated['description'] ?? null,
                'labels' =>
                    $validated['labels'] ?? null,
                'priority' =>
                    $validated['priority'] ?? null,
                'story_points' =>
                    $validated['storyPoint'],
                'time_log' =>
                    $validated['timeLog'],
            ],
            $validated['assignee'] ?? null
        );

        if ($result['move']) {
            broadcast(
                new TaskMoved($result['move'])
            )->toOthers();
        }

        return response()->json([
            'success' => true,
            'task' => $result['task'],
            'move' => $result['move'],
        ]);
    }
}
