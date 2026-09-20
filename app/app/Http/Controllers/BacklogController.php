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
        $criteria = $this->criteria->get('backlog');

        $priority = $criteria['priority'];
        $label = $criteria['label'];
        $sortBy = $criteria['sortField'];
        $sortDirection = $criteria['sortDirection'];

        $backlogId = (int) config(
            'mangie.product_backlog_board_id',
            1
        );

        $backlog = Board::query()
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
                        $query->where('labels', $label);
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
            ])
            ->findOrFail($backlogId);

        Gate::authorize('view', $backlog);

        $backlogIssueCount = $backlog->columns
            ->sum('tasks_count');

        $user = $request->user();

        $boards = Board::query()
            ->with([
                'columns' => fn ($query) =>
                    $query->orderBy('position'),
                'columns.tasks' => fn ($query) =>
                    $query->orderBy('position'),
            ])
            ->where(function ($query) use (
                $user,
                $backlogId
            ) {
                $query
                    ->whereKey($backlogId)
                    ->orWhere(function ($query) use ($user) {
                        $query
                            ->where('user_id', $user->id)
                            ->orWhereHas(
                                'users',
                                fn ($members) =>
                                    $members->whereKey($user->id)
                            );
                    });
            })
            ->orderBy('created_at')
            ->get();

        $inactive_boards = $boards->filter(
            fn (Board $board) =>
                (int) $board->id === $backlogId
                || (
                    !$board->completed
                    && !$board->status
                )
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

        return view($viewName, compact(
            'backlog',
            'backlogIssueCount',
            'boards',
            'inactive_boards',
            'activeSprints',
            'user',
            'cookies'
        ));
    }
}
