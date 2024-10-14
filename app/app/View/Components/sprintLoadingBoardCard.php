<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class sprintLoadingBoardCard extends Component
{
    public $board;
    public $activeSprints;
    /**
     * Create a new component instance.
     */
    public function __construct($board, $activeSprints)
    {
        $this->board = $board;
        $this->activeSprints = $activeSprints;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.sprint-loading-board-card');
    }
}
