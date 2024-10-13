<?php
namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
use App\Models\Column;
use App\Models\Task;
use App\Models\TaskUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BoardController extends Controller
{
    const PRODUCT_BACKLOG_ID = 1;

    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return redirect()->route('login'); // Redirect to login page if not authenticated
        }

        $activeSprints = Board::where('status', 1)->count() > 0;

        // Fetch boards that belong to this user (for example)
        $boards = Board::where('user_id', $user->id)->get();

        // Pass the user and boards to the home view
        return view('home', compact('user', 'boards', 'activeSprints'));
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
        // Fetch the board by ID with its columns and tasks, and sort columns by position
        $board = Board::with(['columns' => function ($query) {
            $query->orderBy('position');
        }, 'columns.tasks'])->findOrFail($id);

        $end = \Carbon\Carbon::parse($board->end_date);
        $now = \Carbon\Carbon::now();

        $daysLeft = ceil($now->diffInDays($end));

        // boolean on whether there are any active sprints
        $activeSprints = Board::where('status', 1)->count() > 0;

        $user = Auth::user();

        // Pass the board to the sprint_board view
        return view('boards.show', compact('board', 'daysLeft', 'activeSprints', 'user'));
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
        // $_COOKIE['cookieName'];
        // Fetch the board by ID with its columns and tasks, and sort columns by position
        $backlog = Board::with(['columns' => function ($query) {
            $query->orderBy('position');
        }, 'columns.tasks'])->findOrFail(1);

        // Get All boards for a user, including the product backlog
        $user = Auth::user();
        $boards = Board::where('user_id', $user->id)->orWhere('id', 1)->get();
        $tasks = Task::all();

        // boolean on whether there are any active sprints
        $activeSprints = Board::where('status', 1)->count() > 0;

        if ($view == 'card'){
            // Pass the boards and tasks to the backlog view
            return view('boards.product_backlog_card_view', compact('backlog', 'tasks', 'boards', 'activeSprints', 'user'));
        } else if ($view == 'list'){
            // Pass the boards and tasks to the backlog view
            return view('boards.product_backlog_list_view', compact('backlog', 'tasks', 'boards', 'activeSprints', 'user'));
        }
    }

    public function sortBacklog(Request $request){
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

    public function filterBacklog(Request $request){
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
        foreach ($task_ids as $task_id){
            array_push($task_id_num, (int)$task_id);
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

        try {
            // Update the board with the new data
            $board->start_date = now();
            $board->end_date = $request->end_date;
            $board->duration = $duration;
            $board->sprint_goal = $request->sprint_goal;
            $board->status = 1;
            $board->updated_at = now();
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
            $board->save();

            return redirect("/home")->with('success', 'Board completed successfully');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete board'
            ], 500);
        }
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
}
