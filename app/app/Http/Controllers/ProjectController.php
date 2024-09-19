<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function sprint()
    {
        // Simulate project data for testing
        $project = new \stdClass();
        $project->name = 'Sample Project';

        $board = new \stdClass();
        $board->name = 'Sample Board';

        $column = new \stdClass();
        $column->name = 'To Do';

        $task = new \stdClass();
        $task->title = 'Sample Task';

        // Set relationships
        $column->tasks = collect([$task]);
        $board->columns = collect([$column]);
        $project->boards = collect([$board]);

        $projects = collect([$project]);

        return view('sprint_board', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'name' => $request->name,
        ]);

        return redirect()->route('sprint_board');
    }

    public function backlog()
    {
        return view("product_backlog");
    }
// #TODO: Revert the test case from above th this one once you populate the database
//     public function index()
// {
//     $projects = Project::with(['boards.columns.tasks'])->get();
//     return view('home', compact('projects'));
// }

}

