<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

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
        $this->task = $task->loadMissing(
            'column.board.columns',
            'column.board.users',
            'users'
        );
        $this->parent_board = $this->task->column->board;
        $this->users = $this->parent_board->users;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.task-detail');
    }
}
