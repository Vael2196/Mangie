<?php

namespace App\Http\Controllers;

use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskMoved;
use App\Events\TaskUpdated;
use App\Models\Column;
use App\Models\BoardLabel;
use App\Models\Task;
use App\Services\TaskMutationService;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        $task->load('column.board', 'users');

        $event = new TaskCreated(
            RealtimePayload::task(
                $task,
                $request->user(),
                [
                    'column_id' => (int) $column->id,
                    'column_count' => $column->tasks()->count(),
                    'backlog_issue_count' => $this->backlogIssueCount(),
                ]
            ),
            (int) $column->board_id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
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
                'max:50',
            ],
            'label_ids' => ['sometimes', 'array', 'max:50'],
            'label_ids.*' => [
                'integer',
                'distinct',
                'exists:board_labels,id',
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
            'start_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'due_complete' => ['sometimes', 'boolean'],
            'sections' => ['sometimes', 'array', 'max:20'],
            'sections.*.title' => [
                'required',
                'string',
                'max:120',
            ],
            'sections.*.content' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'checklists' => ['sometimes', 'array', 'max:20'],
            'checklists.*.title' => [
                'required',
                'string',
                'max:120',
            ],
            'checklists.*.items' => ['sometimes', 'array', 'max:100'],
            'checklists.*.items.*.content' => [
                'required',
                'string',
                'max:500',
            ],
            'checklists.*.items.*.is_complete' => [
                'sometimes',
                'boolean',
            ],
            'expected_version' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ]);

        $task->load('column.board');

        $targetColumn = Column::with('board')->findOrFail(
            $validated['column_id']
        );

        Gate::authorize('update', $task->column->board);
        Gate::authorize('update', $targetColumn->board);

        $labelIds = null;

        if (array_key_exists('label_ids', $validated)) {
            $labelIds = array_map(
                'intval',
                $validated['label_ids']
            );
        } elseif (array_key_exists('labels', $validated)) {
            $legacyLabel = $validated['labels'] ?? null;
            $labelIds = [];

            if ($legacyLabel) {
                $legacyLabelId = BoardLabel::query()
                    ->where('board_id', $targetColumn->board_id)
                    ->where('name', $legacyLabel)
                    ->value('id');

                if ($legacyLabelId) {
                    $labelIds[] = (int) $legacyLabelId;
                }
            }
        }

        if ($labelIds !== null) {
            $validLabelCount = BoardLabel::query()
                ->where('board_id', $targetColumn->board_id)
                ->whereIn('id', $labelIds)
                ->count();

            if ($validLabelCount !== count($labelIds)) {
                throw ValidationException::withMessages([
                    'label_ids' =>
                        'Every selected label must belong to this board.',
                ]);
            }
        }

        $attributes = [
            'title' => $validated['title'],
            'description' =>
                $validated['description'] ?? null,
            'priority' =>
                $validated['priority'] ?? null,
            'story_points' =>
                $validated['storyPoint'],
            'time_log' =>
                $validated['timeLog'],
        ];

        foreach ([
            'start_at',
            'due_at',
            'due_complete',
        ] as $optionalAttribute) {
            if (array_key_exists($optionalAttribute, $validated)) {
                $attributes[$optionalAttribute] =
                    $validated[$optionalAttribute];
            }
        }

        $result = $this->tasks->update(
            $task,
            $targetColumn,
            $attributes,
            $validated['assignee'] ?? null,
            $validated['expected_version'] ?? null,
            $labelIds,
            $validated['sections'] ?? null,
            $validated['checklists'] ?? null
        );

        $events = [];

        if ($result['move']) {
            $move = $result['move'];
            $moveTask = $move['task'];
            unset($move['task']);

            $moveEvent = new TaskMoved(
                RealtimePayload::task(
                    $moveTask,
                    $request->user(),
                    $move
                ),
                [
                    $move['source_board_id'],
                    $move['target_board_id'],
                ]
            );

            broadcast($moveEvent)->toOthers();
            $events[] = [
                'event' => $moveEvent->broadcastAs(),
                'payload' => $moveEvent->payload,
            ];
        }

        $updatedTask = $result['task'];
        $sourceBoardId = $result['move']['source_board_id']
            ?? (int) $updatedTask->column->board_id;
        $targetBoardId = (int) $updatedTask->column->board_id;

        $updatedEvent = new TaskUpdated(
            RealtimePayload::task(
                $updatedTask,
                $request->user()
            ),
            [$sourceBoardId, $targetBoardId]
        );

        broadcast($updatedEvent)->toOthers();
        $events[] = [
            'event' => $updatedEvent->broadcastAs(),
            'payload' => $updatedEvent->payload,
        ];

        return response()->json([
            'success' => true,
            'event' => $updatedEvent->broadcastAs(),
            'payload' => $updatedEvent->payload,
            'mutations' => $events,
            'task' => $updatedTask,
            'move' => $result['move'],
        ]);
    }

    public function destroy(
        Request $request,
        Task $task
    ): JsonResponse {
        $task->load('column.board');
        Gate::authorize('update', $task->column->board);

        $boardId = (int) $task->column->board_id;
        $result = $this->tasks->delete($task);

        $event = new TaskDeleted(
            RealtimePayload::deleted(
                'task',
                $result['task_id'],
                $result['version'],
                $request->user(),
                [
                    'board_id' => $boardId,
                    'column_id' => $result['column_id'],
                    'column_count' => $result['column_count'],
                    'backlog_issue_count' => $this->backlogIssueCount(),
                ]
            ),
            $boardId
        );

        broadcast($event)->toOthers();

        return response()->json($event->response());
    }

    private function backlogIssueCount(): int
    {
        $backlogBoardId = (int) config(
            'mangie.product_backlog_board_id',
            1
        );

        return Task::query()
            ->whereHas(
                'column',
                fn ($query) => $query->where(
                    'board_id',
                    $backlogBoardId
                )
            )
            ->count();
    }
}
