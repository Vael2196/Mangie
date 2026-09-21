<?php

use App\Events\TaskCreated;
use App\Events\TaskMoved;
use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function taskMutationBoard(User $owner): array
{
    $project = Project::create(['name' => 'Realtime Project']);
    $board = Board::create([
        'name' => 'Sprint 1',
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);
    $board->users()->attach($owner);

    $todo = Column::create([
        'name' => 'TO DO',
        'board_id' => $board->id,
        'position' => 1,
    ]);
    $done = Column::create([
        'name' => 'DONE',
        'board_id' => $board->id,
        'position' => 2,
    ]);

    return compact('project', 'board', 'todo', 'done');
}

it('authorizes members and returns the shared mutation contract', function () {
    Event::fake([TaskCreated::class]);

    $owner = User::factory()->create();
    $member = User::factory()->create();
    $outsider = User::factory()->create();
    ['board' => $board, 'todo' => $todo] = taskMutationBoard($owner);
    $board->users()->attach($member);

    $this->actingAs($outsider)
        ->postJson('/tasks/store', [
            'title' => 'Forbidden task',
            'column_id' => $todo->id,
        ])
        ->assertForbidden();

    $response = $this->actingAs($member)
        ->postJson('/tasks/store', [
            'title' => 'Shared task',
            'column_id' => $todo->id,
        ])
        ->assertCreated()
        ->assertJsonPath('event', 'task.created')
        ->assertJsonPath('payload.entity.title', 'Shared task')
        ->assertJsonPath('payload.entity.version', 1)
        ->assertJsonStructure([
            'success',
            'event',
            'payload' => [
                'event_id',
                'occurred_at',
                'entity_type',
                'entity',
                'actor',
                'meta',
            ],
        ]);

    Event::assertDispatched(TaskCreated::class);
    expect($response->json('payload.actor.id'))->toBe($member->id);
});

it('moves a task canonically and increments its version', function () {
    Event::fake([TaskMoved::class]);

    $owner = User::factory()->create();
    ['todo' => $todo, 'done' => $done] = taskMutationBoard($owner);
    $task = Task::create([
        'title' => 'Move me',
        'column_id' => $todo->id,
        'position' => 1,
    ]);

    $this->actingAs($owner)
        ->patchJson("/tasks/{$task->id}/move", [
            'target_column_id' => $done->id,
            'target_position' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('event', 'task.moved')
        ->assertJsonPath('payload.entity.column_id', $done->id)
        ->assertJsonPath('payload.entity.version', 2)
        ->assertJsonPath('payload.meta.source_column_count', 0)
        ->assertJsonPath('payload.meta.target_column_count', 1);

    $task->refresh();

    expect($task->column_id)->toBe($done->id)
        ->and($task->position)->toBe(1)
        ->and($task->version)->toBe(2)
        ->and($task->completed_at)->not->toBeNull();

    Event::assertDispatched(TaskMoved::class);
});

it('rejects a stale task detail save', function () {
    $owner = User::factory()->create();
    ['todo' => $todo] = taskMutationBoard($owner);
    $task = Task::create([
        'title' => 'Current title',
        'column_id' => $todo->id,
        'position' => 1,
        'version' => 3,
    ]);

    $this->actingAs($owner)
        ->patchJson("/tasks/{$task->id}", [
            'column_id' => $todo->id,
            'title' => 'Stale edit',
            'description' => null,
            'assignee' => null,
            'labels' => null,
            'priority' => null,
            'storyPoint' => 0,
            'timeLog' => 0,
            'expected_version' => 2,
        ])
        ->assertConflict();

    expect($task->fresh()->title)->toBe('Current title')
        ->and($task->fresh()->version)->toBe(3);
});
