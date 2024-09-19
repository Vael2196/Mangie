<?php

// namespace App\Http\Controllers;

// use App\Models\Board;
// use Illuminate\Http\Request;

// class BoardController extends Controller
// {
//     /**
//      * Store a newly created board in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @return \Illuminate\Http\Response
//      */
//     public function store(Request $request)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255',
//             'project_id' => 'required|exists:projects,id'
//         ]);

//         $board = new Board([
//             'name' => $request->name,
//             'project_id' => $request->project_id
//         ]);
//         $board->save();

//         return redirect()->back()->with('success', 'Board created successfully.');
//     }

//     /**
//      * Update the specified board in storage.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  int  $id
//      * @return \Illuminate\Http\Response
//      */
//     public function update(Request $request, $id)
//     {
//         $request->validate([
//             'name' => 'required|string|max:255'
//         ]);

//         $board = Board::findOrFail($id);
//         $board->update($request->all());

//         return redirect()->back()->with('success', 'Board updated successfully.');
//     }

//     /**
//      * Remove the specified board from storage.
//      *
//      * @param  int  $id
//      * @return \Illuminate\Http\Response
//      */
//     public function destroy($id)
//     {
//         $board = Board::findOrFail($id);
//         $board->delete();

//         return redirect()->back()->with('success', 'Board deleted successfully.');
//     }
// }


namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Project;
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

        // Create the new board associated with the authenticated user
        $board = Board::create([
            'name' => $request->name,
            'user_id' => Auth::id(),
        ]);

        // Return a JSON response to the front-end
        return response()->json([
            'success' => true,
            'board' => $board,
        ]);
    }
}