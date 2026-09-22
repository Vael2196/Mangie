<?php

use App\Events\BoardUpdated;
use App\Events\ColumnRenamed;
use App\Events\TaskCreated;
use App\Events\TaskMoved;
use App\Events\TaskUpdated;
use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Event;

function taskMutationBoard(User $owner): array
{
    $project = Project::create(['name' => 'Realtime Project']);

    /*
     * Board ID 1 is intentionally the shared Product Backlog. Create it
     * before the sprint so this fixture exercises normal board membership
     * authorization instead of the backlog exception in BoardPolicy.
     */
    $backlog = Board::create([
        'name' => 'Product Backlog',
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);
    $backlog->users()->attach($owner);

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

it('retains private channels after queued event serialization', function () {
    $event = new TaskCreated(
        ['entity' => ['id' => 99]],
        42
    );

    /** @var TaskCreated $restored */
    $restored = unserialize(serialize($event));

    expect($restored->broadcastOn())
        ->toHaveCount(1);
});

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

it('updates a current task detail and increments its integer version', function () {
    Event::fake([TaskUpdated::class]);

    $owner = User::factory()->create();
    ['todo' => $todo] = taskMutationBoard($owner);
    $task = Task::create([
        'title' => 'Old title',
        'column_id' => $todo->id,
        'position' => 1,
    ]);

    $this->actingAs($owner)
        ->patchJson("/tasks/{$task->id}", [
            'column_id' => $todo->id,
            'title' => 'Updated title',
            'description' => 'Updated description',
            'assignee' => null,
            'labels' => 'Backend',
            'priority' => 'High',
            'storyPoint' => 5,
            'timeLog' => 2,
            'expected_version' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('event', 'task.updated')
        ->assertJsonPath('payload.entity.version', 2);

    expect($task->fresh()->title)->toBe('Updated title')
        ->and($task->fresh()->version)->toBe(2);

    Event::assertDispatched(TaskUpdated::class);
});

it('renames a column and increments its integer version', function () {
    Event::fake([ColumnRenamed::class]);

    $owner = User::factory()->create();
    ['todo' => $todo] = taskMutationBoard($owner);

    $this->actingAs($owner)
        ->patchJson("/columns/{$todo->id}/name", [
            'name' => 'READY',
        ])
        ->assertOk()
        ->assertJsonPath('event', 'column.renamed')
        ->assertJsonPath('payload.entity.version', 2);

    expect($todo->fresh()->name)->toBe('READY')
        ->and($todo->fresh()->version)->toBe(2);

    Event::assertDispatched(ColumnRenamed::class);
});

it('updates board status and increments its integer version', function () {
    Event::fake([BoardUpdated::class]);

    $owner = User::factory()->create();
    ['board' => $board] = taskMutationBoard($owner);

    $this->actingAs($owner)
        ->postJson('/boards/updateStatus', [
            'board_id' => $board->id,
            'status' => true,
        ])
        ->assertOk()
        ->assertJsonPath('event', 'board.updated')
        ->assertJsonPath('payload.entity.version', 2);

    expect($board->fresh()->status)->toBeTrue()
        ->and($board->fresh()->version)->toBe(2);

    Event::assertDispatched(BoardUpdated::class);
});
