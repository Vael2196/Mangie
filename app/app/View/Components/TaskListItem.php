<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TaskListItem extends Component
{
    public $task;
    public $index;

    public function __construct($task, $index)
    {
        $this->task = $task;
        $this->index = $index;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.task-list-item');
    }
}
