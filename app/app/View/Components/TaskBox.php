<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Column;

class TaskBox extends Component
{
    public $task;
    public $column;

    public function __construct($task)
    {
        $this->task = $task;
        $this->column = Column::where('id', $task->column_id)->get()[0];
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.task-box');
    }
}
