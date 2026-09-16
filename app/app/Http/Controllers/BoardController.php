<?php
namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
use App\Models\Column;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Charts\BurndownChart;

class BoardController extends Controller
{
    const PRODUCT_BACKLOG_ID = 1;

    // public function index()
    // {
    //     // Get the authenticated user
    //     $user = Auth::user();

    //     // Check if the user is authenticated
    //     if (!$user) {
    //         return redirect()->route('login'); // Redirect to login page if not authenticated
    //     }

    //     $activeSprints = Board::where('status', 1)->count() > 0;

    //     // Fetch boards that belong to this user (for example)
    //     $boards = Board::where('user_id', $user->id)->get();

    //     // end Boards that have passed end_date
    //     foreach ($boards as $board) {
    //         $this->endBoardIfExpired($board);
    //     }

    //     // Pass the user and boards to the home view
    //     return view('home', compact('user', 'boards', 'activeSprints'));
    // }

        public function home()
    {
        return $this->renderBoardsPage('home');
    }

    public function dashboard()
    {
        return $this->renderBoardsPage('dashboard');
    }

    private function renderBoardsPage(string $pageMode)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $activeSprints = Board::where('status', 1)->exists();

        $boards = Board::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        foreach ($boards as $board) {
            $this->endBoardIfExpired($board);
        }

        return view('home', compact(
            'user',
            'boards',
            'activeSprints',
            'pageMode'
        ));
    }


    /**
     * Store a newly created board in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create the new board associated with the authenticated user and project
        $board = Board::create([
            'name' => $request->name,
            'project_id' => 1,  // Use the passed project_id
            'user_id' => Auth::id(),
        ]);

        // Create the default columns
        $columns = ['TO DO', 'DOING', 'DONE'];

        foreach ($columns as $index => $columnName) {
            $board->columns()->create([
                'name' => $columnName,
                'position' => $index + 1,
            ]);
        }

        $board->users()->attach(Auth::id());

        // Return a JSON response to the front-end
        return response()->json([
            'success' => true,
            'board' => $board,
        ]);
    }

    /**
     * Display the specified board with its columns and tasks.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $scope = "board_{$id}";

        $criteria = $this->getTaskCriteria($scope);

        $priority = $criteria['priority'];
        $label = $criteria['label'];
        $sortBy = $criteria['sortField'];
        $sortDirection = $criteria['sortDirection'];

        $board = Board::with([
            'columns' => function ($query) {
                $query->withCount('tasks')->orderBy('position');
            },

            'columns.tasks' => function ($query) use (
                $label,
                $priority,
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
        ])->findOrFail($id);

        $cookies = $criteria['cookies'];

        // Fetch the board by ID with its columns and tasks, and sort columns by position
        $board = Board::with(['columns' => function ($query) {
            $query->withCount('tasks')->orderBy('position');
        }, 'columns.tasks' => function ($query) use ($label, $priority, $sortBy, $sortDirection){
            // Filtering
            if($priority){ $query->Where('priority', $priority); }
            if($label){ $query->Where('labels', $label); }

            // Sort
            if($sortBy && $sortDirection){$query->orderBy($sortBy, $sortDirection);}
            else{$query->orderBy('position');}

        }])->findOrFail($id);

        // parse sort by text to tag names
        $sortByDict = ['title' => 'Title',
                'description' => 'Description',
                'priority' => 'Priority',
                'labels' => 'Labels',
                'story_points' => "Story Points",
                'time_log' => 'Time Log'];
        if(array_key_exists($sortBy, $sortByDict)){
            $sortBy = $sortByDict[$sortBy];
        }

        $cookies = array('label' => $label, 'priority' => $priority, 'sort' => array($sortBy, $sortDirection));


        $end = \Carbon\Carbon::parse($board->end_date);
        $now = \Carbon\Carbon::now();

        $daysLeft = ceil($now->diffInDays($end));

        // boolean on whether there are any active sprints
        $activeSprints = Board::where('status', 1)->count() > 0;

        $user = Auth::user();

        // End the board if it has expired
        $this->endBoardIfExpired($board);

        // Pass the board to the sprint_board view
        return view('boards.show', compact('board', 'daysLeft', 'activeSprints', 'user', 'cookies'));
    }

    private function endBoardIfExpired(Board $board)
    {
        if (\Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($board->end_date))) {
            $board->completed = 1;
            $board->status = 0;
            $board->date_ended = $board->end_date;
            $board->save();
        }
    }

    public function storeColumn(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'board_id' => 'required|exists:boards,id',
        ]);

        // Create a new column
        $column = Column::create([
            'name' => $request->name,
            'board_id' => $request->board_id,
            'position' => Column::where('board_id', $request->board_id)->max('position') + 1,
        ]);

        // Return the new column as a JSON response
        return response()->json([
            'success' => true,
            'column' => $column,
        ]);
    }

    public function updateColumnName(
        Request $request,
        Column $column
    ) {
        if ($column->board?->completed) {
            return response()->json([
                'success' => false,
                'message' => 'Completed sprint columns cannot be renamed.',
            ], 403);
        }

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $column->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,

            'column' => [
                'id' => $column->id,
                'name' => $column->name,
            ],
        ]);
    }

    public function updateColumnColor(Request $request, Column $column) {
        $validated = $request->validate([
            'color' => [
                'required',
                'string',
                'in:gray,blue,green,yellow,orange,red,purple,pink',
            ],
        ]);

        $column->update([
            'color' => $validated['color'],
        ]);

        return response()->json([
            'success' => true,
            'column' => $column,
        ]);
    }

    public function copyColumn(Column $column)
    {
        $column->load([
            'tasks' => function ($query) {
                $query->orderBy('position');
            },
            'tasks.users',
        ]);

        $copiedColumn = DB::transaction(
            function () use ($column) {

                Column::where(
                    'board_id',
                    $column->board_id
                )
                    ->where(
                        'position',
                        '>',
                        $column->position
                    )
                    ->increment('position');

                $newColumn = $column->replicate([
                    'id',
                    'position',
                    'created_at',
                    'updated_at',
                ]);

                $newColumn->name =
                    $column->name . ' copy';

                $newColumn->position =
                    $column->position + 1;

                $newColumn->save();

                foreach ($column->tasks as $task) {

                    $newTask = $task->replicate([
                        'id',
                        'column_id',
                        'position',
                        'created_at',
                        'updated_at',
                    ]);

                    $newTask->column_id =
                        $newColumn->id;

                    $newTask->position =
                        $task->position;

                    $newTask->completed_at = null;

                    $newTask->save();

                    $newTask->users()->sync(
                        $task->users
                            ->pluck('id')
                            ->all()
                    );
                }


                return $newColumn;
            }
        );


        return response()->json([
            'success' => true,
            'column' => $copiedColumn,
        ]);
    }

    public function destroyColumn(Column $column)
    {
        if ($column->tasks()->exists()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This column cannot be deleted because it contains tasks.',
            ], 422);
        }

        DB::transaction(function () use ($column) {

            $boardId =
                $column->board_id;

            $position =
                $column->position;

            $column->delete();

            Column::where(
                'board_id',
                $boardId
            )
                ->where(
                    'position',
                    '>',
                    $position
                )
                ->decrement('position');
        });

        return response()->json([
            'success' => true,
        ]);
    }

    public function destroy($id)
    {
        // where id=id
        Board::where('id', $id)->firstOrFail()->delete();

        return redirect()->route('home')->with('success', 'Board deleted successfully');
    }

    public function storeTask(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'column_id' => 'required|exists:columns,id',
        ]);

        // Create the new task
        $task = Task::create([
            'title' => $request->title,
            'column_id' => $request->column_id,
            'position' => Task::where('column_id', $request->column_id)->max('position') + 1,
        ]);

        // Return the new task as a JSON response
        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    public function showBacklog($view)
    {
        $criteria =
            $this->getTaskCriteria('backlog');

        $priority =
            $criteria['priority'];

        $label =
            $criteria['label'];

        $sortBy =
            $criteria['sortField'];

        $sortDirection =
            $criteria['sortDirection'];


        $backlog = Board::with([
            'columns' => function ($query) {
                $query
                    ->withCount('tasks')
                    ->orderBy('position');
            },

            'columns.tasks' => function ($query) use (
                $label,
                $priority,
                $sortBy,
                $sortDirection
            ) {
                if ($priority !== '') {
                    $query->where(
                        'priority',
                        $priority
                    );
                }

                if ($label !== '') {
                    $query->where(
                        'labels',
                        $label
                    );
                }

                if (
                    $sortBy !== '' &&
                    $sortDirection !== ''
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
            ->findOrFail(
                self::PRODUCT_BACKLOG_ID
            );

        $backlogIssueCount =
            $backlog->columns
                ->sum('tasks_count');


        $sortByDict = [
            'title' => 'Title',
            'description' => 'Description',
            'priority' => 'Priority',
            'labels' => 'Labels',
            'story_points' => 'Story Points',
            'time_log' => 'Time Log',
        ];

        $sortTag =
            $sortByDict[$sortBy] ?? $sortBy;

        $cookies = [
            'label' => $label,
            'priority' => $priority,
            'sort' => [
                $sortTag,
                $sortDirection,
            ],
        ];


        $user = Auth::user();

        $boards = Board::with([
            'columns' => function ($query) {
                $query->orderBy('position');
            },

            'columns.tasks' => function ($query) {
                $query->orderBy('position');
            },
        ])
            ->where(function ($query) use ($user) {
                $query
                    ->where(
                        'user_id',
                        $user->id
                    )
                    ->orWhere(
                        'id',
                        self::PRODUCT_BACKLOG_ID
                    );
            })
            ->get();


        $inactive_boards =
            $boards->filter(
                fn ($board) =>
                    $board->id
                        === self::PRODUCT_BACKLOG_ID
                    || (
                        !$board->completed
                        && !$board->status
                    )
            );


        $activeSprints =
            $boards->contains(
                fn ($board) =>
                    $board->id
                        !== self::PRODUCT_BACKLOG_ID
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


        return view(
            $viewName,
            compact(
                'backlog',
                'backlogIssueCount',
                'boards',
                'inactive_boards',
                'activeSprints',
                'user',
                'cookies'
            )
        );
    }

    public function moveTasks(Request $request){
        $request->validate([
            'board_id' => 'required|exists:boards,id',
        ]);

        // Get the column to move the tasks to
        if ($request->board_id == 1){
            $todo_column = Column::where('board_id', 1)
                            ->where('name', "Backlog")->get()[0];
        }else{
            $todo_column = Column::where('board_id', $request->board_id)
            ->where('name', "TO DO")->get()[0];
        }

        // Parse task_ids string to php array
        $task_id_string = $request->task_ids;
        $task_id_string = str_replace('[', '', $task_id_string);
        $task_id_string = str_replace(']', '', $task_id_string);
        $task_ids = explode(',', $task_id_string);

        // Convert task_ids to integer
        $task_id_num = [];
        foreach ($task_ids as $task_id){
            array_push($task_id_num, (int)$task_id);
        }

        // Updating the column id and positions of each task
        $res = Task::whereIn('id', $task_id_num)->update([
            'column_id' => $todo_column->id,
            'position' => Task::where('column_id', $todo_column->id)->max('position') + 1,
            'updated_at' => now()
        ]);

        // Check is update success
        if (!$res){
            return response()->json([
                'success' => false,
                'message' => 'Failed to move tasks'
            ]);
        }

        // Fetch the new tasks
        $tasks = Task::whereIn('id', $task_id_num)->get();

        // Return the new tasks as a JSON response
        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ]);
    }

    public function moveTask(Request $request, Task $task)
    {
        $validated = $request->validate([
            'target_column_id' => [
                'required',
                'integer',
                'exists:columns,id',
            ],
        ]);

        $targetColumn = Column::with('board')
            ->findOrFail($validated['target_column_id']);

        $task->load('column.board');

        $sourceColumnId = $task->column_id;

        if ($targetColumn->board?->completed) {
            return response()->json([
                'success' => false,
                'message' => 'Tasks cannot be moved into a completed sprint.',
            ], 422);
        }

        if ($sourceColumnId === $targetColumn->id) {
            return response()->json([
                'success' => true,
                'task' => $task,
                'source_column_count' =>
                    Task::where('column_id', $sourceColumnId)->count(),
                'target_column_count' =>
                    Task::where('column_id', $targetColumn->id)->count(),
                'backlog_issue_count' =>
                    Task::whereHas('column', function ($query) {
                        $query->where(
                            'board_id',
                            self::PRODUCT_BACKLOG_ID
                        );
                    })->count(),
            ]);
        }

        DB::transaction(function () use (
            $task,
            $sourceColumnId,
            $targetColumn
        ) {
            $oldPosition = $task->position ?? 0;

            Task::where('column_id', $sourceColumnId)
                ->where('position', '>', $oldPosition)
                ->decrement('position');

            $newPosition =
                (Task::where(
                    'column_id',
                    $targetColumn->id
                )->max('position') ?? 0) + 1;

            $task->column_id = $targetColumn->id;
            $task->position = $newPosition;

            if (
                strtoupper(trim($targetColumn->name))
                === 'DONE'
            ) {
                $task->completed_at = now();
            } else {
                $task->completed_at = null;
            }

            $task->save();
        });

        return response()->json([
            'success' => true,

            'task' => $task->fresh(),

            'source_column_id' => $sourceColumnId,

            'target_column_id' => $targetColumn->id,

            'source_column_count' =>
                Task::where(
                    'column_id',
                    $sourceColumnId
                )->count(),

            'target_column_count' =>
                Task::where(
                    'column_id',
                    $targetColumn->id
                )->count(),

            'backlog_issue_count' =>
                Task::whereHas('column', function ($query) {
                    $query->where(
                        'board_id',
                        self::PRODUCT_BACKLOG_ID
                    );
                })->count(),
        ]);
    }

    public function moveColumnTasks(Request $request){
        $request->validate([
            'column_id' => 'required|exists:columns,id',
        ]);

        // Parse task_ids string to php array
        $task_id_string = $request->task_ids;
        $task_id_string = str_replace('[', '', $task_id_string);
        $task_id_string = str_replace(']', '', $task_id_string);
        $task_ids = explode(',', $task_id_string);

        // Convert task_ids to integer
        $task_id_num = [];
        $column = Column::where('id', $request->column_id)->get()[0];
        foreach ($task_ids as $task_id){
            array_push($task_id_num, (int)$task_id);

            // Complete task
            if ($column && $column->name == "DONE") {
                $this->completeTask((int)$task_id, $request->column_id);
            }
        }

        // Updating the column id and positions of each task
        $res = Task::whereIn('id', $task_id_num)->update([
            'column_id' => $request->column_id,
            'position' => Task::where('column_id', $request->column_id)->max('position') + 1,
            'updated_at' => now()
        ]);

        // Check is update success
        if (!$res){
            return response()->json([
                'success' => false,
                'message' => 'Failed to move tasks'
            ]);
        }

        // Fetch the new tasks
        $tasks = Task::whereIn('id', $task_id_num)->get();

        // Return the new tasks as a JSON response
        return response()->json([
            'success' => true,
            'tasks' => $tasks
        ]);
    }

    public function updateTask(Request $request){
        $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'column_id' => 'required|exists:columns,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee' => 'integer',
            'labels' => 'nullable|string',
            'priority' => 'nullable|string',
            'storyPoint' => 'integer',
            'timeLog' => 'integer',
        ]);

        $column = Column::find($request->column_id);

        if ($column && $column->name == "DONE") {
            $this->completeTask($request->task_id, $request->column_id);
        }


        Log::info('Request data:', $request->all());

        // Update the task
        $res = Task::where('id', $request->task_id)->update([
            'title' => $request->title,
            'column_id' => $request->column_id,
            'description' => $request->description,
            'labels' => $request->labels,
            'priority' => $request->priority,
            'story_points' => $request->storyPoint,
            'time_log' => $request->timeLog,
            'updated_at' => now()
        ]);

        // TaskUser::create([
        //     'task_id' => $request->task_id,
        //     'user_id' => $request->assignee,
        //     'created_at' => now(),
        //     'updated_at' => now()
        // ]);

        // Check is update success
        if (!$res){
            return response()->json([
                'success' => false,
                'message' => 'Failed to move tasks'
            ]);
        }

        // Fetch the new task
        $task = Task::where('id', $request->task_id)->get();

        // Return the updated task as a JSON response
        return response()->json([
            'success' => true,
            'task' => $task,
        ]);
    }

    protected function completeTask($task_id, $column_id){
        $task = Task::findOrFail($task_id);
        $column = Column::findOrFail($column_id);

        if ($column && $column->name == "DONE") {
            Log::info('Task moved to DONE column:', [
                'task_id' => $task_id,
                'column_id' => $column_id,
            ]);

            $task->completed_at = now();
            $task->save();
        } else {
            Log::info('Task moved to a non-DONE column:', [
                'task_id' => $task_id,
                'column_id' => $column_id,
            ]);

            if ($task->completed_at) {
                $task->completed_at = null;
                $task->save();
            }
        }
    }

    // function to updates boards
    public function updateStatus(Request $request)
    {
        // Validate the input
        $request->validate([
            'board_id' => 'required|exists:boards,id',   // Make sure the board exists
            'status' => 'required|boolean',              // Status must be boolean (0 or 1)
        ]);

        Log::info('Request data:', $request->all());

        // Find the board by ID
        $board = Board::find($request->board_id);

        Log::info('Found board:', ['board' => $board]);

        // Check if the board exists
        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board not found'
            ], 404);
        }

        try {
            $updated = $board->update([
                'status' => $request->status,    // Update the status
                'updated_at' => now()             // Update the timestamp
            ]);

            $board->status = $request->status;
            $board->updated_at = now();
            $board->save();

            Log::info('Board updated:', ['board' => $board]);

            // Check if the update was successful
            if ($updated) {
                Log::info('Board updated successfully', ['board_id' => $board->id, 'new_status' => $request->status]);
                return response()->json([
                    'success' => true,
                    'message' => 'Board status updated successfully'
                ]);
            } else {
                Log::error('Failed to update board', ['board_id' => $board->id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update board status'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Update failed: ' . $e->getMessage(), ['board_id' => $board->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update board status',
                'board' => $board,
            ], 500);
        }
    }

    public function startSprint(Request $request)
    {
        Log::info('startSprint called:', $request->all());

        // Validate the input
        $request->validate([
            'board_id' => 'required|exists:boards,id',
            'end_date' => 'required|date|after_or_equal:today',
            'sprint_goal' => 'required|string|max:255',
        ]);

        Log::info('Request data:', $request->all());

        // Find the board by ID
        $board = Board::find($request->board_id);

        // Convert dates to Carbon
        $start = \Carbon\Carbon::parse($request->start_date);
        $end = \Carbon\Carbon::parse($request->end_date);

        $duration = $start->diffInDays($end);

        if (!$board) {
            return redirect()->back()->with('error', 'Board not found.');
        }

        $totalStoryPoints = 0;

        foreach ($board->columns as $column) {
            foreach ($column->tasks as $task) {
                $totalStoryPoints += $task->story_points;
            }
        }

        try {
            // Update the board with the new data
            $board->start_date = now();
            $board->end_date = $request->end_date;
            $board->duration = $duration;
            $board->sprint_goal = $request->sprint_goal;
            $board->status = 1;
            $board->updated_at = now();
            $board->total_story_points = $totalStoryPoints;
            $board->save();

            Log::info('Board updated:', ['board' => $board]);

            return redirect()->back()->with('success', 'Board activated and updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update board: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update board.');
        }
    }

    public function completeBoard($id)
    {
        // Find the board by ID
        $board = Board::findOrFail($id);

        if (!$board) {
            return response()->json([
                'success' => false,
                'message' => 'Board not found'
            ], 404);
        }

        // $incompleteTasks = Task::where('position', $id)
        // @foreach ($board->columns as $column)
        //     @foreach ($column->tasks as $task)
        //         if ($task->position == 1){
        //             return redirect()->back()->with('error', 'Board cannot be completed with incomplete tasks.');
        //         }
        //     @endforeach
        // @endforeach

        foreach ($board->columns as $column) {
            // Check if the column is NOT the "DONE" column
            if ($column->name !== "DONE") {
                // Move all tasks from this column back to the product backlog
                foreach ($column->tasks as $task) {
                    // Move task to product backlog
                    $task->column_id = self::PRODUCT_BACKLOG_ID;
                    $task->save();
                }
            }
        }

        try {
            // Update the board with the new data
            $board->completed = 1;
            $board->status = 0;
            $board->updated_at = now();
            $board->date_ended = now();
            $board->save();

            return redirect("/home")->with('success', 'Board completed successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete board'
            ], 500);
        }
    }

    public function showBurndownChart($board_id)
    {
        $board = Board::findOrFail($board_id);
        $tasks = [];

        $start = \Carbon\Carbon::parse($board->start_date);
        $end = \Carbon\Carbon::parse($board->date_ended);

        foreach ($board->columns as $column) {
            foreach ($column->tasks as $task) {
                $tasks[] = $task;
            }
        }

        $totalStoryPoints = $board->total_story_points;

        $labels = [];
        $storyPointsData = [];
        $expectedVelocityData = [];
        $remainingStoryPoints = $totalStoryPoints;
        for ($date = $start; $date->lte($end); $date->addDay()) {
            $labels[] = $date->format('Y-m-d');

            // Deduct the story points of tasks completed by this date
            foreach ($tasks as $task) {
                if ($task->completed_at && \Carbon\Carbon::parse($task->completed_at)->lte($date)) {
                    $remainingStoryPoints -= $task->story_points;
                    // Remove the task from the list so it doesn't get deducted again
                    // $tasks = $tasks->reject(fn($t) => $t->id == $task->id);
                    $tasks = array_filter($tasks, function ($t) use ($task) {
                        return $t->id !== $task->id;
                    });
                }
            }



            // Add the current remaining story points to the data
            $storyPointsData[] = $remainingStoryPoints;
        }

        $expectedIncrement = $totalStoryPoints / (count($labels) - 1);
        for ($i = 0; $i < count($labels); $i++) {
            $expectedVelocityData[] = $totalStoryPoints - ($expectedIncrement * $i);
        }

        Log::info('Chart stuff', [
            'labels' => $labels,
            'storyPointsData' => $storyPointsData,
        ]);

        $chart = new BurndownChart();
        $chart->labels($labels);
        $chart->dataset('Actual Velocity', 'line', $storyPointsData)
            ->color('rgb(255, 99, 132)')
            ->backgroundcolor('rgba(255, 99, 132, 0.2)');
        $chart->dataset('Expected Velocity', 'line', $expectedVelocityData)
            ->color('rgb(54, 162, 235)')
            ->backgroundcolor('rgba(54, 162, 235, 0.2)');

        $user = Auth::user();




        return view('boards.burndown_chart', compact('chart', 'board', 'user'));
    }

    public function sortTasks(Request $request){
        $request->validate([
            'param' => 'required|string',
            'order' => 'required|string',
        ]);

        $tasks = Task::orderBy($request->param, $request->order)->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function filterTasks(Request $request){
        $request->validate([
            'param' => 'required|string',
            'filter' => 'required|string',
            'amount' => 'required|string',
        ]);

        $tasks = Task::where($request->param, $request->filter, $request->amount)->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    }

    public function searchUsers(Request $request){
        $query = $request->input('query');

        $users = User::where('name', 'like', "%{$query}%")->get();

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    public function addUserToBoard(Request $request, Board $board)
    {
        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $user = User::findOrFail(
            $validated['user_id']
        );

        $alreadyAdded = $board
            ->users()
            ->where('users.id', $user->id)
            ->exists();

        if ($alreadyAdded) {
            return response()->json([
                'success' => false,
                'message' => 'User is already added to this board.',
            ], 409);
        }

        $board->users()->syncWithoutDetaching([
            $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User added successfully.',
            'user' => $user,
        ]);
    }

    private function getTaskCriteria(string $scope): array {
        $readCookie = function (string $name) use ($scope): string {
            $value = $_COOKIE["{$scope}_{$name}"] ?? '';

            return rawurldecode($value);
        };

        $priority = $readCookie('priority');
        $label = $readCookie('label');
        $sortField = $readCookie('sort');
        $sortDirection = $readCookie('direction');

        $validPriorities = [
            'Low',
            'Medium',
            'High',
        ];

        $validLabels = [
            'API',
            'Backend',
            'Frontend',
            'UI/UX',
            'Database',
        ];

        $sortLabels = [
            'title' => 'Title',
            'description' => 'Description',
            'priority' => 'Priority',
            'labels' => 'Labels',
            'story_points' => 'Story Points',
            'time_log' => 'Time Log',
        ];

        if (!in_array($priority, $validPriorities, true)) {
            $priority = '';
        }

        if (!in_array($label, $validLabels, true)) {
            $label = '';
        }

        if (!array_key_exists($sortField, $sortLabels)) {
            $sortField = '';
        }

        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = '';
        }

        return [
            'priority' => $priority,
            'label' => $label,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,

            'cookies' => [
                'priority' => $priority,
                'label' => $label,

                'sort' => [
                    $sortField !== ''
                        ? $sortLabels[$sortField]
                        : '',

                    $sortDirection,
                ],
            ],
        ];
    }

}

