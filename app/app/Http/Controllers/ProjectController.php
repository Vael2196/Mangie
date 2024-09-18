<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
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

        return view('dashboard', compact('projects'));
    }
// #TODO: Revert the test case from above th this one once you populate the database
//     public function index()
// {
//     $projects = Project::with(['boards.columns.tasks'])->get();
//     return view('home', compact('projects'));
// }

}

