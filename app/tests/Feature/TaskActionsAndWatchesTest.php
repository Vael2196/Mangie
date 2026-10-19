<?php

use App\Events\TaskCreated;
use App\Events\TaskMoved;
use App\Events\TaskUpdated;
use App\Jobs\SendTaskWatchEmail;
use App\Mail\TaskWatchMail;
use App\Models\Board;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskWatch;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

function taskActionFixture(): array
{
    Event::fake([TaskCreated::class, TaskUpdated::class, TaskMoved::class]);
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $observer = User::factory()->create();
    $project = Project::create(['name' => 'Action project']);
    $board = Board::create(['name' => 'Actions', 'project_id' => $project->id, 'user_id' => $owner->id]);
    $board->users()->attach([$member->id => ['role' => 'member'], $observer->id => ['role' => 'observer']]);
    $column = $board->columns()->create(['name' => 'TO DO', 'position' => 1]);
    $next = $board->columns()->create(['name' => 'DOING', 'position' => 2]);
    $task = $column->tasks()->create(['title' => 'A detailed task', 'description' => 'Keep the details', 'position' => 1]);

    return compact('owner', 'member', 'observer', 'board', 'column', 'next', 'task');
}

function taskActionPayload($task, array $extra = []): array
{
    return ['column_id' => $task->column_id, 'title' => $task->title, 'description' => $task->description,
        'storyPoint' => 0, 'timeLog' => 0, 'expected_version' => $task->fresh()->version, ...$extra];
}

it('joins without replacing other assignees and saves multiple assignees together', function () {
    ['owner' => $owner, 'member' => $member, 'task' => $task] = taskActionFixture();
    $task->users()->attach($owner->id);
    $this->actingAs($member)->patchJson("/tasks/{$task->id}/actions", ['joined' => true, 'expected_version' => 1])
        ->assertOk()->assertJsonPath('payload.entity.assignee_ids', [$owner->id, $member->id]);
    $this->actingAs($owner)->patchJson("/tasks/{$task->id}", taskActionPayload($task, ['assignee_ids' => [$owner->id, $member->id]]))->assertOk();
    expect($task->users()->count())->toBe(2);
    $this->patchJson("/tasks/{$task->id}", taskActionPayload($task, ['description' => 'Preserve assignments']))->assertOk();
    expect($task->users()->count())->toBe(2);
    $this->actingAs($member)->patchJson("/tasks/{$task->id}/actions", ['joined' => false, 'expected_version' => $task->fresh()->version])->assertOk();
    expect($task->users()->pluck('users.id')->all())->toBe([$owner->id]);
});

it('rejects outsiders and observers as assignees and refuses observer mutations', function () {
    ['owner' => $owner, 'observer' => $observer, 'task' => $task] = taskActionFixture();
    $outsider = User::factory()->create();
    foreach ([$observer->id, $outsider->id] as $id) {
        $this->actingAs($owner)->patchJson("/tasks/{$task->id}", taskActionPayload($task, ['assignee_ids' => [$id]]))
            ->assertUnprocessable()->assertJsonValidationErrors('assignee_ids');
    }
    $this->actingAs($observer)->getJson("/tasks/{$task->id}/actions")->assertOk()->assertJsonPath('can_edit', false);
    $this->patchJson("/tasks/{$task->id}/actions", ['joined' => true, 'expected_version' => 1])->assertForbidden();
    $this->postJson("/tasks/{$task->id}/copy", ['expected_version' => 1])->assertForbidden();
    $this->actingAs($outsider)->getJson("/tasks/{$task->id}/actions")->assertForbidden();
    $this->putJson("/tasks/{$task->id}/watch", ['watching' => true])->assertForbidden();
});

it('persists completion and colour and rejects stale quick actions and menu moves', function () {
    ['owner' => $owner, 'task' => $task, 'next' => $next] = taskActionFixture();
    $this->actingAs($owner)->patchJson("/tasks/{$task->id}/actions", ['completed' => true, 'color' => 'purple', 'expected_version' => 1])
        ->assertOk()->assertJsonPath('payload.entity.color', 'purple')->assertJsonPath('payload.entity.version', 2);
    expect($task->fresh()->completed_at)->not->toBeNull();
    $this->patchJson("/tasks/{$task->id}/actions", ['color' => 'red', 'expected_version' => 1])->assertConflict();
    $this->patchJson("/tasks/{$task->id}/move", ['target_column_id' => $next->id, 'target_position' => 1, 'expected_version' => 1])->assertConflict();
    $this->patchJson("/tasks/{$task->id}/move", ['target_column_id' => $next->id, 'target_position' => 1, 'expected_version' => 2])->assertOk();
    expect($task->fresh()->completed_at)->not->toBeNull();
    $this->patchJson("/tasks/{$task->id}/actions", ['completed' => false, 'color' => null, 'expected_version' => $task->fresh()->version])->assertOk();
    expect($task->fresh()->completed_at)->toBeNull()->and($task->fresh()->color)->toBeNull();
});

it('copies every detail with an independent cover without copying watchers', function () {
    Storage::fake('local');
    ['owner' => $owner, 'member' => $member, 'board' => $board, 'task' => $task] = taskActionFixture();
    Storage::disk('local')->put('task-covers/original.png', 'image bytes');
    $task->forceFill(['cover_image_path' => 'task-covers/original.png', 'color' => 'green', 'start_at' => now(), 'due_at' => now()->addDay()])->save();
    $task->users()->attach([$owner->id, $member->id]);
    $label = $board->labels()->create(['name' => 'Copy me', 'color' => '#6366f1', 'position' => 1]);
    $task->boardLabels()->attach($label);
    $task->sections()->create(['title' => 'Notes', 'content' => 'Important section', 'position' => 1]);
    $list = $task->checklists()->create(['title' => 'Steps', 'position' => 1]);
    $list->items()->create(['content' => 'Done already', 'is_complete' => true, 'position' => 1]);
    TaskWatch::create(['task_id' => $task->id, 'user_id' => $member->id]);
    $response = $this->actingAs($owner)->postJson("/tasks/{$task->id}/copy", ['expected_version' => 1])->assertCreated();
    $copy = Task::findOrFail($response->json('payload.entity.id'));
    expect($copy->title)->toBe('A detailed task copy')->and($copy->description)->toBe($task->description)
        ->and($copy->color)->toBe('green')->and($copy->version)->toBe(1)->and($copy->position)->toBe(2)
        ->and($copy->users()->count())->toBe(2)->and($copy->boardLabels()->sole()->id)->toBe($label->id)
        ->and($copy->sections()->sole()->content)->toBe('Important section')
        ->and($copy->checklists()->sole()->items()->sole()->is_complete)->toBeTrue()
        ->and(TaskWatch::where('task_id', $copy->id)->count())->toBe(0)
        ->and($copy->cover_image_path)->not->toBe($task->cover_image_path);
    Storage::disk('local')->assertExists($copy->cover_image_path);
    $this->deleteJson("/tasks/{$copy->id}")->assertOk();
    Storage::disk('local')->assertExists($task->cover_image_path);
});

it('records watched changes while ignoring the watchers own edits', function () {
    ['owner' => $owner, 'member' => $member, 'task' => $task] = taskActionFixture();
    $this->actingAs($member)->putJson("/tasks/{$task->id}/watch", ['watching' => true])->assertOk();
    $this->patchJson("/tasks/{$task->id}", taskActionPayload($task, ['description' => 'My edit']))->assertOk();
    expect(TaskWatch::sole()->pending_since)->toBeNull();
    $this->actingAs($owner)->patchJson("/tasks/{$task->id}", taskActionPayload($task, ['description' => 'Another editor']))->assertOk();
    expect(TaskWatch::sole()->pending_since)->not->toBeNull()->and(TaskWatch::sole()->change_count)->toBe(1);
    $this->actingAs($member)->putJson("/tasks/{$task->id}/watch", ['watching' => false])->assertOk();
    expect(TaskWatch::count())->toBe(0);
});

it('sends a single digest when the chosen interval has passed', function (string $frequency, int $minutes) {
    Mail::fake();
    ['member' => $member, 'task' => $task] = taskActionFixture();
    $member->forceFill(['notification_frequency' => $frequency])->save();
    $watch = TaskWatch::create(['user_id' => $member->id, 'task_id' => $task->id,
        'pending_since' => now()->subMinutes(max(0, $minutes - 1)), 'change_count' => 3]);
    (new SendTaskWatchEmail($watch->id))->handle();
    if ($minutes > 0) {
        Mail::assertNothingSent();
        $this->travel(2)->minutes();
        (new SendTaskWatchEmail($watch->id))->handle();
    }
    Mail::assertSent(TaskWatchMail::class, fn ($mail) => $mail->hasTo($member->email) && $mail->changeCount === 3);
    expect($watch->fresh()->pending_since)->toBeNull();
    (new SendTaskWatchEmail($watch->id))->handle();
    Mail::assertSentCount(1);
})->with([
    ['instantly', 0], ['30_minutes', 30], ['1_hour', 60], ['3_hours', 180],
    ['5_hours', 300], ['12_hours', 720], ['1_day', 1440], ['1_week', 10080],
]);

it('does not send queued mail after access is removed or notifications are disabled', function () {
    Mail::fake();
    ['member' => $member, 'board' => $board, 'task' => $task] = taskActionFixture();
    $watch = TaskWatch::create(['user_id' => $member->id, 'task_id' => $task->id, 'pending_since' => now()->subDay(), 'change_count' => 2]);
    $board->users()->detach($member);
    (new SendTaskWatchEmail($watch->id))->handle();
    Mail::assertNothingSent();
    expect($watch->fresh())->toBeNull();
    $board->users()->attach($member, ['role' => 'member']);
    $member->forceFill(['notification_frequency' => 'never'])->save();
    $watch = TaskWatch::create(['user_id' => $member->id, 'task_id' => $task->id, 'pending_since' => now()->subDay(), 'change_count' => 2]);
    (new SendTaskWatchEmail($watch->id))->handle();
    Mail::assertNothingSent();
    expect($watch->fresh()->pending_since)->toBeNull();
});

it('queues due digests and lets observers watch without editing the task', function () {
    Queue::fake();
    ['observer' => $observer, 'task' => $task] = taskActionFixture();
    $this->actingAs($observer)->putJson("/tasks/{$task->id}/watch", ['watching' => true])->assertOk();
    TaskWatch::sole()->update(['pending_since' => now()->subHours(4), 'change_count' => 1]);
    $this->artisan('mangie:send-watch-notifications')->assertSuccessful();
    Queue::assertPushed(SendTaskWatchEmail::class, 1);
});

it('queues instant watches after the mutation commits and renders the email', function () {
    Queue::fake();
    ['owner' => $owner, 'member' => $member, 'task' => $task] = taskActionFixture();
    $member->forceFill(['notification_frequency' => 'instantly'])->save();
    $watch = TaskWatch::create(['user_id' => $member->id, 'task_id' => $task->id]);
    $this->actingAs($owner)->patchJson("/tasks/{$task->id}/actions", ['color' => 'blue', 'expected_version' => 1])->assertOk();
    Queue::assertPushed(SendTaskWatchEmail::class, fn ($job) => $job->watchId === $watch->id);
    $mail = new TaskWatchMail($watch->fresh()->load('task.column.board', 'actor'));
    expect($mail->render())->toContain('A detailed task', '?task='.$task->id, 'Change email frequency');
});

it('keeps manually completed tasks in the sprint and returns unchecked DONE tasks', function () {
    ['owner' => $owner, 'board' => $backlog, 'column' => $backlogColumn] = taskActionFixture();
    $backlog->update(['is_backlog' => true]);
    $sprint = Board::create(['name' => 'Completion test', 'project_id' => $backlog->project_id, 'user_id' => $owner->id]);
    $todo = $sprint->columns()->create(['name' => 'TO DO', 'position' => 1]);
    $done = $sprint->columns()->create(['name' => 'DONE', 'position' => 2]);
    $completed = $todo->tasks()->create(['title' => 'Completed by checkbox', 'completed_at' => now(), 'position' => 1]);
    $reopened = $done->tasks()->create(['title' => 'Unchecked in DONE', 'position' => 1]);
    $this->actingAs($owner)->post("/boards/{$sprint->id}/endSprint")->assertRedirect();
    expect($completed->fresh()->column_id)->toBe($todo->id)->and($reopened->fresh()->column_id)->toBe($backlogColumn->id);
});

it('checks deletion versions and updates the backlog count after a copy', function () {
    ['owner' => $owner, 'task' => $task] = taskActionFixture();
    $this->actingAs($owner)->deleteJson("/tasks/{$task->id}", ['expected_version' => 2])->assertConflict();
    $task->column->board->update(['is_backlog' => true]);
    $this->postJson("/tasks/{$task->id}/copy", ['expected_version' => 1])->assertCreated()->assertJsonPath('payload.meta.backlog_issue_count', 2);
});
