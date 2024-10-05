<?php
namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{

    public function index()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is authenticated
        if (!$user) {
            return redirect()->route('login'); // Redirect to login page if not authenticated
        }

        // Fetch boards that belong to this user (for example)
        $boards = Board::where('user_id', $user->id)->get();

        // Pass the user and boards to the home view
        return view('home', compact('user', 'boards'));
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

        // Pass the board to the sprint_board view
        return view('boards.show', compact('board'));
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

    public function showBacklog()
    {
        // Fetch the board by ID with its columns and tasks, and sort columns by position
        $backlog = Board::with(['columns' => function ($query) {
            $query->orderBy('position');
        }, 'columns.tasks'])->findOrFail(1);

        // Get All boards for a user, including the product backlog
        $user = Auth::user();
        $boards = Board::where('user_id', $user->id)->orWhere('id', 1)->get();

        $tasks = Task::all();

        // Pass the boards and tasks to the backlog view
        return view('boards.product_backlog', compact('backlog', 'tasks', 'boards'));
    }

    public function moveTasks(Request $request){
        $request->validate([
            // 'task_ids' => 'array:tasks,id',
            'board_id' => 'required|exists:boards,id',
        ]);

        // $output = $request;
        // if (is_array($output)){
        //     $output = implode(',', $output);
        // }
        // echo "<script>console.log('Debug Objects: " . $output . "' );</script>";


        $todo_column = Column::where('board_id', $request->board_id)
                            ->where('name', "TO DO")->get()[0];

        $task_id_string = $request->task_ids;
        $task_id_string = str_replace('[', '', $task_id_string);
        $task_id_string = str_replace(']', '', $task_id_string);
        $task_ids = explode(',', $task_id_string);
        $task_id_num = [];
        foreach ($task_ids as $task_id){
            array_push($task_id_num, (int)$task_id);
        }

        // Find the initial board ids for each task
        $initial_board_ids = Column::select('board_id')->where('id', Task::select('column_id')->whereIn('id', $task_id_num))->get();

        // Updating the column id and positions of each task
        $res = Task::whereIn('id', $task_id_num)->update([
            'column_id' => $todo_column->id,
            'position' => Task::where('column_id', $todo_column->id)->max('position') + 1,
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

        return response()->json([
            'success' => true,
            'task_ids' => $task_ids,
            'type' => $task_id_num,
            // "column_id" => $todo_column->id,
            "starting_board_id" => $initial_board_ids, // Arr({board_id: board_id})
            'tasks' => $tasks
        ]);
    }
}
