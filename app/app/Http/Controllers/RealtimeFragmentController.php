<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use App\Services\TaskCriteriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class RealtimeFragmentController extends Controller
{
    public function __construct(
        private readonly TaskCriteriaService $criteria
    ) {
    }

    public function task(
        Request $request,
        Task $task,
        string $variant
    ): JsonResponse {
        abort_unless(in_array($variant, ['card', 'list'], true), 404);

        $task->load('column.board', 'users');
        Gate::authorize('view', $task->column->board);

        $view = $variant === 'card'
            ? 'boards.partials.task-card'
            : 'boards.partials.task-list-row';

        return response()->json([
            'success' => true,
            'html' => view($view, compact('task'))->render(),
        ]);
    }

    public function detail(
        Task $task
    ): JsonResponse {
        $task->load(
            'column.board.columns',
            'column.board.users',
            'users'
        );

        Gate::authorize('view', $task->column->board);

        return response()->json([
            'success' => true,
            'html' => view(
                'boards.partials.task-detail',
                compact('task')
            )->render(),
        ]);
    }

    public function column(
        Request $request,
        Column $column
    ): JsonResponse {
        $column->load('board');
        Gate::authorize('view', $column->board);

        $criteria = $this->criteria->get(
            $request,
            "board_{$column->board_id}"
        );

        $tasks = $column->tasks()
            ->with('users')
            ->when(
                $criteria['priority'] !== '',
                fn ($query) => $query->where(
                    'priority',
                    $criteria['priority']
                )
            )
            ->when(
                $criteria['label'] !== '',
                fn ($query) => $query->where(
                    'labels',
                    $criteria['label']
                )
            )
            ->when(
                $criteria['sortField'] !== '',
                fn ($query) => $query->orderBy(
                    $criteria['sortField'],
                    $criteria['sortDirection']
                ),
                fn ($query) => $query->orderBy('position')
            )
            ->get();

        $column->setRelation('tasks', $tasks);
        $column->setAttribute(
            'tasks_count',
            $column->tasks()->count()
        );

        $board = $column->board;
        $editable = !$board->completed;
        $criteriaActive = $criteria['active'];

        return response()->json([
            'success' => true,
            'html' => view(
                'boards.partials.column',
                compact(
                    'column',
                    'board',
                    'editable',
                    'criteriaActive'
                )
            )->render(),
        ]);
    }

    public function board(
        Request $request,
        Board $board,
        string $variant
    ): JsonResponse {
        abort_unless(in_array($variant, [
            'home-card',
            'home-list',
            'backlog-card',
            'backlog-list',
        ], true), 404);

        Gate::authorize('view', $board);

        $board->load([
            'columns' => fn ($query) => $query->orderBy('position'),
            'columns.tasks' => fn ($query) => $query->orderBy('position'),
            'columns.tasks.users',
        ]);

        $activeSprints = Board::query()
            ->accessibleTo($request->user())
            ->where('project_id', $board->project_id)
            ->where('status', true)
            ->where('completed', false)
            ->exists();

        return response()->json([
            'success' => true,
            'html' => view(
                "boards.partials.{$variant}",
                compact('board', 'activeSprints')
            )->render(),
        ]);
    }
}
