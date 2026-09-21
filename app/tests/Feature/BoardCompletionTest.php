<?php

use App\Events\BoardCompleted;
use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Event;

it('returns unfinished tasks to the actual backlog column', function () {
    Event::fake([BoardCompleted::class]);

    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Completion Project']);

    $backlog = Board::create([
        'name' => 'Product Backlog',
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);
    $backlog->users()->attach($owner);

    $throwaway = Column::create([
        'name' => 'Throwaway',
        'board_id' => $backlog->id,
        'position' => 1,
    ]);
    $throwaway->delete();

    $backlogColumn = Column::create([
        'name' => 'Backlog',
        'board_id' => $backlog->id,
        'position' => 1,
    ]);

    expect($backlogColumn->id)->not->toBe($backlog->id);

    $sprint = Board::create([
        'name' => 'Sprint to complete',
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'status' => true,
    ]);
    $sprint->users()->attach($owner);

    $todo = Column::create([
        'name' => 'TO DO',
        'board_id' => $sprint->id,
        'position' => 1,
    ]);
    $done = Column::create([
        'name' => 'DONE',
        'board_id' => $sprint->id,
        'position' => 2,
    ]);

    $unfinished = Task::create([
        'title' => 'Return me',
        'column_id' => $todo->id,
        'position' => 1,
    ]);
    $finished = Task::create([
        'title' => 'Keep me',
        'column_id' => $done->id,
        'position' => 1,
        'completed_at' => now(),
    ]);

    $this->actingAs($owner)
        ->post("/boards/{$sprint->id}/endSprint")
        ->assertRedirect(route('home'));

    expect($unfinished->fresh()->column_id)->toBe($backlogColumn->id)
        ->and($unfinished->fresh()->version)->toBe(2)
        ->and($finished->fresh()->column_id)->toBe($done->id)
        ->and($sprint->fresh()->completed)->toBeTrue()
        ->and($sprint->fresh()->status)->toBeFalse()
        ->and($sprint->fresh()->version)->toBe(2);

    Event::assertDispatched(BoardCompleted::class);
});
