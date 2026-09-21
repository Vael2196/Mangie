<?php

namespace App\Http\Controllers;

use App\Charts\BurndownChart;
use App\Events\BoardCompleted;
use App\Events\BoardCreated;
use App\Events\BoardDeleted;
use App\Events\BoardUpdated;
use App\Models\Board;
use App\Services\BoardLifecycleService;
use App\Services\TaskCriteriaService;
use App\Support\Realtime\RealtimePayload;
use App\Support\Realtime\TaskRealtimeData;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BoardController extends Controller
{
    public function __construct(
        private readonly BoardLifecycleService $lifecycle,
        private readonly TaskCriteriaService $criteria
    ) {
    }

    public function home(Request $request): View
    {
        return $this->renderBoardsPage($request, 'home');
    }

    public function dashboard(Request $request): View
    {
        return $this->renderBoardsPage($request, 'dashboard');
    }

    private function renderBoardsPage(
        Request $request,
        string $pageMode
    ): View {
        $user = $request->user();

        $boards = Board::query()
            ->accessibleTo($user)
            ->orderByDesc('created_at')
            ->get();

        foreach ($boards as $board) {
            $this->lifecycle->endIfExpired($board);
        }

        $activeSprints = $boards->contains(
            fn (Board $board) =>
                !$board->completed && $board->status
        );

        $projectId = (int) ($boards->first()?->project_id ?? 1);

        return view('home', compact(
            'user',
            'boards',
            'activeSprints',
            'pageMode',
            'projectId'
        ));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'project_id' => [
                'required',
                'integer',
                'exists:projects,id',
            ],
        ]);

        $board = DB::transaction(function () use (
            $request,
            $validated
        ) {
            $board = Board::create([
                'name' => $validated['name'],
                'project_id' => $validated['project_id'],
                'user_id' => $request->user()->id,
            ]);

            foreach (
                ['TO DO', 'DOING', 'DONE']
                as $index => $columnName
            ) {
                $board->columns()->create([
                    'name' => $columnName,
                    'position' => $index + 1,
                    'color' => 'gray',
                ]);
            }

            $board->users()->syncWithoutDetaching([
                $request->user()->id,
            ]);

            return $board->load('columns');
        }, 3);

        $event = new BoardCreated(
            RealtimePayload::board(
                $board,
                $request->user()
            ),
            (int) $board->project_id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
        ], 201);
    }

    public function show(
        Request $request,
        Board $board
    ): View {
        Gate::authorize('view', $board);

        $this->lifecycle->endIfExpired($board);

        $criteria = $this->criteria->get(
            $request,
            "board_{$board->id}"
        );

        $priority = $criteria['priority'];
        $label = $criteria['label'];
        $sortBy = $criteria['sortField'];
        $sortDirection = $criteria['sortDirection'];

        $board->load([
            'users',
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

                if ($sortBy !== '' && $sortDirection !== '') {
                    $query->orderBy($sortBy, $sortDirection);
                } else {
                    $query->orderBy('position');
                }
            },
        ]);

        $daysLeft = null;

        if ($board->end_date) {
            $daysLeft = (int) ceil(
                now()
                    ->startOfDay()
                    ->diffInDays(
                        Carbon::parse($board->end_date)
                            ->startOfDay(),
                        false
                    )
            );
        }

        $activeSprints = Board::query()
            ->where('project_id', $board->project_id)
            ->where('status', true)
            ->where('completed', false)
            ->exists();

        $user = $request->user();
        $cookies = $criteria['tags'];
        $taskCriteria = $criteria;
        $projectId = (int) $board->project_id;

        return view('boards.show', compact(
            'board',
            'daysLeft',
            'activeSprints',
            'user',
            'cookies',
            'taskCriteria',
            'projectId'
        ));
    }

    public function updateStatus(
        Request $request
    ): JsonResponse {
        $validated = $request->validate([
            'board_id' => [
                'required',
                'integer',
                'exists:boards,id',
            ],
            'status' => ['required', 'boolean'],
        ]);

        $board = Board::findOrFail(
            $validated['board_id']
        );

        Gate::authorize('update', $board);

        $board->update([
            'status' => $validated['status'],
            'version' => DB::raw('version + 1'),
        ]);

        $board->refresh();

        $event = new BoardUpdated(
            RealtimePayload::board(
                $board,
                $request->user()
            ),
            (int) $board->project_id,
            (int) $board->id
        );

        broadcast($event)->toOthers();

        return response()->json([
            ...$event->response(),
            'message' =>
                'Board status updated successfully.',
        ]);
    }

    public function startSprint(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'board_id' => [
                'required',
                'integer',
                'exists:boards,id',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'sprint_goal' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $board = Board::findOrFail(
            $validated['board_id']
        );

        Gate::authorize('update', $board);

        $board = $this->lifecycle->start(
            $board,
            $validated['end_date'],
            $validated['sprint_goal']
        );

        $event = new BoardUpdated(
            RealtimePayload::board(
                $board,
                $request->user()
            ),
            (int) $board->project_id,
            (int) $board->id
        );

        broadcast($event)->toOthers();

        return back()->with(
            'success',
            'Board activated and updated successfully.'
        );
    }

    public function completeBoard(
        Request $request,
        Board $board
    ): RedirectResponse {
        Gate::authorize('update', $board);

        $result = $this->lifecycle->complete($board);
        $board = $result['board'];
        $backlogBoardId = (int) config(
            'mangie.product_backlog_board_id',
            1
        );

        $event = new BoardCompleted(
            RealtimePayload::board(
                $board,
                $request->user(),
                [
                    'moved_tasks' => $result['moved_tasks']
                        ->map(fn ($task) => TaskRealtimeData::from($task))
                        ->all(),
                    'backlog_board_id' => $backlogBoardId,
                    'backlog_issue_count' => \App\Models\Task::query()
                        ->whereHas(
                            'column',
                            fn ($query) => $query->where(
                                'board_id',
                                $backlogBoardId
                            )
                        )
                        ->count(),
                ]
            ),
            (int) $board->project_id,
            [(int) $board->id, $backlogBoardId]
        );

        broadcast($event)->toOthers();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Board completed successfully.'
            );
    }

    public function destroy(
        Request $request,
        Board $board
    ): RedirectResponse {
        Gate::authorize('delete', $board);

        $boardId = (int) $board->id;
        $projectId = (int) $board->project_id;
        $payload = RealtimePayload::deleted(
            'board',
            $boardId,
            (int) $board->version + 1,
            $request->user(),
            ['project_id' => $projectId]
        );

        $board->delete();

        broadcast(new BoardDeleted(
            $payload,
            $projectId,
            $boardId
        ))->toOthers();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'Board deleted successfully.'
            );
    }

    public function showBurndownChart(
        Request $request,
        Board $board
    ): View {
        Gate::authorize('view', $board);

        $board->load('columns.tasks');

        $start = Carbon::parse(
            $board->start_date ?? $board->created_at
        )->startOfDay();

        $end = Carbon::parse(
            $board->date_ended
            ?? $board->end_date
            ?? now()
        )->startOfDay();

        if ($end->lt($start)) {
            $end = $start->copy();
        }

        $tasks = $board->columns
            ->flatMap->tasks
            ->values();

        $totalStoryPoints =
            (int) $board->total_story_points;

        $labels = [];
        $storyPointsData = [];
        $remainingStoryPoints = $totalStoryPoints;
        $remainingTasks = $tasks->all();

        for (
            $date = $start->copy();
            $date->lte($end);
            $date->addDay()
        ) {
            $labels[] = $date->format('Y-m-d');

            foreach ($remainingTasks as $key => $task) {
                if (
                    $task->completed_at
                    && Carbon::parse($task->completed_at)
                        ->lte($date->copy()->endOfDay())
                ) {
                    $remainingStoryPoints -=
                        (int) $task->story_points;

                    unset($remainingTasks[$key]);
                }
            }

            $storyPointsData[] =
                max(0, $remainingStoryPoints);
        }

        $intervalCount = max(count($labels) - 1, 1);
        $expectedIncrement =
            $totalStoryPoints / $intervalCount;
        $expectedVelocityData = [];

        foreach ($labels as $index => $label) {
            $expectedVelocityData[] = max(
                0,
                $totalStoryPoints
                    - ($expectedIncrement * $index)
            );
        }

        $chart = new BurndownChart();
        $chart->labels($labels);

        $chart
            ->dataset(
                'Actual Velocity',
                'line',
                $storyPointsData
            )
            ->color('rgb(255, 99, 132)')
            ->backgroundcolor(
                'rgba(255, 99, 132, 0.2)'
            );

        $chart
            ->dataset(
                'Expected Velocity',
                'line',
                $expectedVelocityData
            )
            ->color('rgb(54, 162, 235)')
            ->backgroundcolor(
                'rgba(54, 162, 235, 0.2)'
            );

        $user = $request->user();

        return view(
            'boards.burndown_chart',
            compact('chart', 'board', 'user')
        );
    }
}
