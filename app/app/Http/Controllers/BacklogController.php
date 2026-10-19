<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Services\TaskCriteriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BacklogController extends Controller
{
    public function __construct(
        private readonly TaskCriteriaService $criteria
    ) {
    }

    public function show(
        Request $request,
        string $view
    ): View {
        $criteria = $this->criteria->get(
            $request,
            'backlog'
        );

        $priority = $criteria['priority'];
        $label = $criteria['label'];
        $sortBy = $criteria['sortField'];
        $sortDirection = $criteria['sortDirection'];

        abort_unless(in_array($view, ['card', 'list'], true), 404);
        $backlogId = app(\App\Services\BacklogService::class)->forUser($request->user())->id;

        $backlog = Board::query()
            ->accessibleTo($request->user())
            ->with([
                'columns' => function ($query) {
                    $query
                        ->withCount('tasks')
                        ->orderBy('position');
                },
                'columns.tasks' => function ($query) use (
                    $priority,
                    $label,
                    $sortBy,
                    $sortDirection
                ) {
                    if ($priority !== '') {
                        $query->where('priority', $priority);
                    }

                    if ($label !== '') {
                        $query->whereHas(
                            'boardLabels',
                            fn ($labels) => $labels->whereKey($label)
                        );
                    }

                    if (
                        $sortBy !== ''
                        && $sortDirection !== ''
                    ) {
                        $query->orderBy(
                            $sortBy,
                            $sortDirection
                        );
                    } else {
                        $query->orderBy('position');
                    }
                },
                'labels',
                'columns.tasks.users',
                'columns.tasks.boardLabels',
                'columns.tasks.checklists.items',
            ])
            ->findOrFail($backlogId);

        $canEditBacklog = $backlog && Gate::allows('update', $backlog);
        $backlogIssueCount = $backlog->columns
            ->sum('tasks_count');

        $user = $request->user();

        $boards = Board::query()
            ->with([
                'columns' => fn ($query) =>
                    $query->orderBy('position'),
                'columns.tasks' => fn ($query) =>
                    $query->orderBy('position'),
                'columns.tasks.users',
                'columns.tasks.boardLabels',
                'columns.tasks.checklists.items',
            ])
            ->accessibleTo($user)
            ->where(fn ($q) => $q->where('boards.id', $backlogId)
                ->orWhere(fn ($sprints) => $sprints->sprints()))
            ->orderBy('created_at')
            ->get();

        $inactive_boards = $boards->filter(
            fn (Board $board) =>
                Gate::allows('update', $board) && ((int) $board->id === $backlogId
                || (
                    !$board->completed
                    && !$board->status
                ))
        );

        $activeSprints = $boards->contains(
            fn (Board $board) =>
                (int) $board->id !== $backlogId
                && !$board->completed
                && $board->status
        );

        $viewName = match ($view) {
            'card' =>
                'boards.product_backlog_card_view',
            'list' =>
                'boards.product_backlog_list_view',
            default => abort(404),
        };

        $cookies = $criteria['tags'];
        $cookies['label'] = $label === ''
            ? ''
            : ($backlog->labels->firstWhere('id', $label)?->name ?? '');
        $taskCriteria = $criteria;
        $sharedBacklogs = Board::accessibleTo($user)->backlogs()
            ->where('boards.id', '<>', $backlogId)->orderBy('name')->get(['boards.id', 'boards.name']);
        $projectId = (int) ($backlog->project_id ?? $boards->first()?->project_id ?? 1);

        return view($viewName, compact(
            'backlog',
            'backlogIssueCount',
            'boards',
            'inactive_boards',
            'activeSprints',
            'user',
            'cookies',
            'taskCriteria',
            'projectId',
            'sharedBacklogs',
            'canEditBacklog'
        ));
    }
}
