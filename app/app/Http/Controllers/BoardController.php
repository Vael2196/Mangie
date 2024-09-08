<?php

namespace App\Http\Controllers;

use App\Models\Board;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    /**
     * Store a newly created board in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id'
        ]);

        $board = new Board([
            'name' => $request->name,
            'project_id' => $request->project_id
        ]);
        $board->save();

        return redirect()->back()->with('success', 'Board created successfully.');
    }

    /**
     * Update the specified board in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $board = Board::findOrFail($id);
        $board->update($request->all());

        return redirect()->back()->with('success', 'Board updated successfully.');
    }

    /**
     * Remove the specified board from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $board = Board::findOrFail($id);
        $board->delete();

        return redirect()->back()->with('success', 'Board deleted successfully.');
    }
}
