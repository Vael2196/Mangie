<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
            // Create a test project object
            $project = new \stdClass(); // \stdClass is a generic PHP object
            $project->name = 'Sample Project';

            // Create a test board
            $board = new \stdClass();
            $board->name = 'Sample Board';

            // Create a test column
            $column = new \stdClass();
            $column->name = 'To Do';

            // Create a test task
            $task = new \stdClass();
            $task->title = 'Sample Task';

            // Simulate relationships: tasks within columns, columns within boards, boards within projects
            $column->tasks = collect([$task]); // The tasks belong to the column
            $board->columns = collect([$column]); // The columns belong to the board
            $project->boards = collect([$board]); // The boards belong to the project

            // Collect the project to simulate multiple projects
            $projects = collect([$project]);

            // Pass the test data to the view
            return view('home', compact('projects'));
    }
// #TODO: Revert the test case from above th this one once you populate the database
//     public function index()
// {
//     $projects = Project::with(['boards.columns.tasks'])->get();
//     return view('home', compact('projects'));
// }

}

