<?php

namespace App\Mail;

use App\Models\TaskWatch;
use Illuminate\Mail\Mailable;

class TaskWatchMail extends Mailable
{
    public string $taskTitle;

    public string $boardName;

    public ?string $actorName;

    public int $changeCount;

    public string $taskUrl;

    public function __construct(TaskWatch $watch)
    {
        $this->taskTitle = $watch->task->title;
        $this->boardName = $watch->task->column->board->name;
        $this->actorName = $watch->actor?->name;
        $this->changeCount = $watch->change_count;
        $this->taskUrl = route('boards.show', $watch->task->column->board_id).'?task='.$watch->task_id;
    }

    public function build(): static
    {
        return $this->subject('Task updated: '.$this->taskTitle)->markdown('emails.task-watch');
    }
}
