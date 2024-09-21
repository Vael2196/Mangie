<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        return view('/boards/dashboard', compact('projects'));
    }

    public function createProject(Request $request)
    {
        // Validate the request to ensure 'name' is provided
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Insert a new project using raw SQL
        DB::table('projects')->insert([
            'name' => $request->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Return a success message or redirect
        return response()->json(['success' => true, 'message' => 'Project created successfully']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project = Project::create([
            'name' => $request->name,
        ]);

        return redirect()->route('/boards/dashboard');
    }
// #TODO: Revert the test case from above th this one once you populate the database
//     public function index()
// {
//     $projects = Project::with(['boards.columns.tasks'])->get();
//     return view('home', compact('projects'));
// }

}

