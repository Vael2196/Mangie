<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class sprintLoadingBoard extends Component
{
    public $board;
    /**
     * Create a new component instance.
     */
    public function __construct($board)
    {
        $this->board = $board;
    }
    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sprint-loading-board');
    }
}
