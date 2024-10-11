<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Column;
use App\Models\Board;
use App\Models\User;

class TaskDetail extends Component
{

    public $task;
    public $users;
    public $parent_board;
    /**
     * Create a new component instance.
     */
    public function __construct($task)
    {
        $this->task = $task;
        $this->users = User::all();
        $this->parent_board = Board::where('id', Column::select('board_id')->where('id', $task->column_id)->get()[0]->board_id)->get()[0];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.task-detail');
    }
}
