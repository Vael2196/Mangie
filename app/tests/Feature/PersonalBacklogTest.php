<?php

use App\Events\BoardCompleted;
use App\Events\TaskCreated;
use App\Events\TaskDeleted;
use App\Events\TaskMoved;
use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use App\Services\BacklogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

function privateBacklogScenario(): array
{
    Event::fake([TaskCreated::class, TaskDeleted::class, TaskMoved::class, BoardCompleted::class]);
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $project = Project::create(['name' => 'Private backlog project']);
    $legacy = Board::create([
        'name' => 'Original private backlog', 'is_backlog' => true,
        'project_id' => $project->id, 'user_id' => $owner->id,
    ]);
    $column = $legacy->columns()->create(['name' => 'Backlog', 'position' => 1]);
    $secret = $column->tasks()->create(['title' => 'Original private work', 'position' => 1]);
    $sprint = Board::create([
        'name' => 'My sprint', 'project_id' => $project->id, 'user_id' => $viewer->id,
    ]);
    $todo = $sprint->columns()->create(['name' => 'TO DO', 'position' => 1]);

    return compact('owner', 'viewer', 'legacy', 'column', 'secret', 'sprint', 'todo');
}

it('gives an excluded verified user a writable private backlog in both views', function (string $view) {
    $s = privateBacklogScenario();
    $this->actingAs($s['viewer'])->get("/backlog/{$view}")
        ->assertOk()->assertDontSeeText('Original private work')
        ->assertDontSeeText('ask its admin for an invitation');
    $backlog = app(BacklogService::class)->forUser($s['viewer']);
    $column = $backlog->columns()->sole();
    expect($backlog->id)->not->toBe($s['legacy']->id)
        ->and($backlog->isBacklog())->toBeTrue()
        ->and($backlog->roleFor($s['viewer']))->toBe('admin')
        ->and($backlog->roleFor($s['owner']))->toBeNull()
        ->and($s['legacy']->roleFor($s['viewer']))->toBeNull();
    $this->get("/backlog/{$view}")->assertOk()
        ->assertSee('data-backlog-board-id="'.$backlog->id.'"', false)
        ->assertSee('data-column-id="'.$column->id.'"', false)
        ->assertDontSee('sprint-loading-board-'.$backlog->id.'"', false);
    $this->postJson('/tasks/store', ['title' => 'My private work', 'column_id' => $column->id])
        ->assertCreated()->assertJsonPath('payload.meta.backlog_board_id', $backlog->id)
        ->assertJsonPath('payload.meta.backlog_issue_count', 1);
    $this->get("/backlog/{$view}")->assertSeeText('My private work');
    $this->getJson('/boards/'.$s['legacy']->id)->assertForbidden();
    $this->postJson('/tasks/store', ['title' => 'Forbidden', 'column_id' => $s['column']->id])->assertForbidden();
    $this->actingAs($s['owner'])->getJson('/boards/'.$backlog->id)->assertForbidden();
    expect(Board::backlogs()->where('user_id', $s['viewer']->id)->count())->toBe(1)
        ->and($s['secret']->fresh()->column_id)->toBe($s['column']->id);
})->with(['list', 'card']);

it('keeps explicitly authorised editors on the existing shared backlog', function () {
    $s = privateBacklogScenario();
    $s['legacy']->users()->attach($s['viewer']->id, ['role' => 'member']);
    $this->actingAs($s['viewer'])->get('/backlog/list')->assertOk()->assertSeeText('Original private work');
    expect($s['viewer']->fresh()->backlog_board_id)->toBe($s['legacy']->id)
        ->and(Board::backlogs()->count())->toBe(1)
        ->and($s['legacy']->roleFor($s['viewer']))->toBe('member');
});

it('gives legacy observers their own backlog without promoting them', function () {
    $s = privateBacklogScenario();
    $s['legacy']->users()->attach($s['viewer']->id, ['role' => 'observer']);
    $this->actingAs($s['viewer'])->get('/backlog/list')->assertOk()->assertSeeText('Shared backlogs:')
        ->assertSeeText('Original private backlog')->assertDontSeeText('Original private work');
    expect($s['viewer']->fresh()->backlog_board_id)->not->toBe($s['legacy']->id)
        ->and($s['legacy']->roleFor($s['viewer']))->toBe('observer');
    $this->get('/boards/'.$s['legacy']->id)->assertOk()->assertSeeText('Original private work');
    $this->postJson('/tasks/store', ['title' => 'No promotion', 'column_id' => $s['column']->id])->assertForbidden();
});

it('moves tasks into and out of the actual personal backlog and scopes all counts', function () {
    $s = privateBacklogScenario();
    $this->actingAs($s['viewer'])->get('/backlog/list')->assertOk();
    $backlog = app(BacklogService::class)->forUser($s['viewer']);
    $column = $backlog->columns()->sole();
    $task = $s['todo']->tasks()->create(['title' => 'Drag me home', 'position' => 1]);
    $this->patchJson('/tasks/'.$task->id.'/move', [
        'target_column_id' => $column->id, 'target_position' => 1, 'expected_version' => 1,
    ])->assertOk()->assertJsonPath('payload.entity.column_id', $column->id)
        ->assertJsonPath('backlog_board_id', $backlog->id)->assertJsonPath('backlog_issue_count', 1);
    $copy = $this->postJson('/tasks/'.$task->id.'/copy', ['expected_version' => 2])
        ->assertCreated()->assertJsonPath('payload.meta.backlog_board_id', $backlog->id)
        ->assertJsonPath('payload.meta.backlog_issue_count', 2)->json('payload.entity.id');
    $this->deleteJson('/tasks/'.$copy, ['expected_version' => 1])->assertOk()
        ->assertJsonPath('payload.meta.backlog_issue_count', 1);
    $this->patchJson('/tasks/'.$task->id.'/move', [
        'target_column_id' => $s['todo']->id, 'target_position' => 1, 'expected_version' => 2,
    ])->assertOk()->assertJsonPath('backlog_issue_count', 0);
    $this->getJson('/realtime/summary')->assertOk()->assertJsonPath('meta.backlog_board_id', $backlog->id)
        ->assertJsonPath('meta.backlog_issue_count', 0);
    $s['sprint']->users()->attach($s['owner']->id, ['role' => 'member']);
    $this->actingAs($s['owner'])->getJson('/realtime/entities/task/'.$task->id)->assertOk()
        ->assertJsonPath('payload.meta.backlog_board_id', $s['legacy']->id)
        ->assertJsonPath('payload.meta.backlog_issue_count', 1);
});

it('cannot move another users private task into a personal backlog', function () {
    $s = privateBacklogScenario();
    $this->actingAs($s['viewer'])->get('/backlog/list');
    $column = app(BacklogService::class)->forUser($s['viewer'])->columns()->sole();
    $this->patchJson('/tasks/'.$s['secret']->id.'/move', [
        'target_column_id' => $column->id, 'target_position' => 1,
    ])->assertForbidden();
    expect($s['secret']->fresh()->column_id)->toBe($s['column']->id);
});

it('never lists any backlog as a sprint on home or dashboard', function () {
    $s = privateBacklogScenario();
    $this->actingAs($s['viewer'])->get('/home')->assertOk();
    $backlog = app(BacklogService::class)->forUser($s['viewer']);
    $s['legacy']->users()->attach($s['viewer']->id, ['role' => 'observer']);
    foreach (['/home?filters=1&show_inactive=1&ownership[]=owned&ownership[]=shared', '/dashboard'] as $url) {
        $this->get($url)->assertOk()->assertSeeText('My sprint')
            ->assertDontSee('id="sprint-card-'.$backlog->id.'"', false)
            ->assertDontSee('id="sprint-card-'.$s['legacy']->id.'"', false);
    }
});

it('returns unfinished sprint work to its creators actual backlog', function () {
    $s = privateBacklogScenario();
    $this->actingAs($s['viewer'])->get('/backlog/list');
    $column = app(BacklogService::class)->forUser($s['viewer'])->columns()->sole();
    $task = $s['todo']->tasks()->create(['title' => 'Unfinished', 'position' => 1]);
    $this->post('/boards/'.$s['sprint']->id.'/endSprint')->assertRedirect(route('home'));
    expect($task->fresh()->column_id)->toBe($column->id)
        ->and($s['secret']->fresh()->column_id)->toBe($s['column']->id);
});

it('does not transfer shared sprint work into the completing members private backlog', function () {
    $s = privateBacklogScenario();
    $s['sprint']->users()->attach($s['owner']->id, ['role' => 'member']);
    app(BacklogService::class)->forUser($s['viewer']);
    $task = $s['todo']->tasks()->create(['title' => 'Shared unfinished work', 'position' => 1]);
    $this->actingAs($s['owner'])->post('/boards/'.$s['sprint']->id.'/endSprint')->assertRedirect();
    expect($task->fresh()->column_id)->toBe($s['todo']->id)
        ->and($s['sprint']->fresh()->completed)->toBeTrue()
        ->and($s['column']->tasks()->count())->toBe(1);
});

it('protects backlogs with nonlegacy IDs against sprint actions and deletion', function () {
    $s = privateBacklogScenario();
    $this->actingAs($s['viewer'])->get('/backlog/list');
    $backlog = app(BacklogService::class)->forUser($s['viewer']);
    $this->get('/boards/'.$backlog->id)->assertOk()->assertDontSeeText('No sprint goal has been set yet.');
    $this->postJson('/boards/startSprint', [
        'board_id' => $backlog->id, 'sprint_goal' => 'No', 'end_date' => now()->addWeek()->toDateString(),
    ])->assertUnprocessable();
    $this->postJson('/boards/updateStatus', ['board_id' => $backlog->id, 'status' => true])->assertUnprocessable();
    $this->postJson('/boards/'.$backlog->id.'/endSprint')->assertUnprocessable();
    $this->deleteJson('/boards/'.$backlog->id)->assertForbidden();
    expect($backlog->fresh()->completed)->toBeFalse()->and($backlog->fresh()->status)->toBeFalse();
});

it('reuses a personal backlog after its preference pointer is cleared', function () {
    $s = privateBacklogScenario();
    $service = app(BacklogService::class);
    $backlog = $service->forUser($s['viewer']);
    DB::table('users')->where('id', $s['viewer']->id)->update(['backlog_board_id' => null]);
    expect($service->forUser($s['viewer'])->id)->toBe($backlog->id)
        ->and(Board::backlogs()->where('user_id', $s['viewer']->id)->count())->toBe(1);
});

it('creates no backlog for unverified users', function () {
    $user = User::factory()->unverified()->create();
    $this->actingAs($user)->get('/backlog/list')->assertRedirect(route('verification.notice'));
    expect(Board::count())->toBe(0)->and(app(BacklogService::class)->current($user))->toBeNull();
});

it('allows account deletion with an exclusive backlog and cleans its images', function () {
    Storage::fake('public');
    Storage::fake('local');
    $user = User::factory()->create();
    $this->actingAs($user)->get('/backlog/card')->assertOk();
    $backlog = app(BacklogService::class)->forUser($user);
    $backlog->update(['background_image_path' => 'board-backgrounds/test.jpg']);
    $backlog->columns()->sole()->tasks()->create([
        'title' => 'Private', 'position' => 1,
    ])->forceFill(['cover_image_path' => 'task-covers/test.jpg'])->save();
    Storage::disk('public')->put('board-backgrounds/test.jpg', 'image');
    Storage::disk('local')->put('task-covers/test.jpg', 'image');
    $this->delete('/profile', ['password' => 'password'])->assertRedirect('/')->assertSessionHasNoErrors();
    expect($user->fresh())->toBeNull()->and($backlog->fresh())->toBeNull();
    Storage::disk('public')->assertMissing('board-backgrounds/test.jpg');
    Storage::disk('local')->assertMissing('task-covers/test.jpg');
});

it('still requires a successor admin before deleting an account with a shared backlog', function () {
    $user = User::factory()->create();
    $backlog = app(BacklogService::class)->forUser($user);
    $backlog->users()->attach(User::factory()->create()->id, ['role' => 'member']);
    $this->actingAs($user)->deleteJson('/profile', ['password' => 'password'])->assertUnprocessable();
    expect($user->fresh())->not->toBeNull()->and($backlog->fresh())->not->toBeNull();
});

it('upgrades the legacy backlog without sharing its data or changing memberships', function () {
    $s = privateBacklogScenario();
    $member = User::factory()->create();
    $observer = User::factory()->create();
    $s['legacy']->users()->attach([$member->id => ['role' => 'member'], $observer->id => ['role' => 'observer']]);
    $before = DB::table('board_user')->orderBy('id')->get()->toJson();
    $migration = require database_path('migrations/2026_10_20_000000_add_user_backlogs.php');
    $migration->down();
    $migration->up();
    expect($s['owner']->fresh()->backlog_board_id)->toBe($s['legacy']->id)
        ->and($member->fresh()->backlog_board_id)->toBe($s['legacy']->id)
        ->and($observer->fresh()->backlog_board_id)->toBeNull()
        ->and($s['viewer']->fresh()->backlog_board_id)->toBeNull()
        ->and($s['legacy']->fresh()->isBacklog())->toBeTrue()
        ->and($s['sprint']->fresh()->isBacklog())->toBeFalse()
        ->and($s['secret']->fresh()->column_id)->toBe($s['column']->id)
        ->and(DB::table('board_user')->orderBy('id')->get()->toJson())->toBe($before);
});

it('replaces a revoked primary backlog without restoring the old membership', function () {
    $s = privateBacklogScenario();
    $s['legacy']->users()->attach($s['viewer']->id, ['role' => 'member']);
    $service = app(BacklogService::class);
    expect($service->forUser($s['viewer'])->id)->toBe($s['legacy']->id);
    $s['legacy']->users()->detach($s['viewer']->id);
    $this->actingAs($s['viewer'])->get('/backlog/list')->assertOk()->assertDontSeeText('Original private work');
    $replacement = $service->forUser($s['viewer']);
    expect($replacement->id)->not->toBe($s['legacy']->id)
        ->and($replacement->roleFor($s['viewer']))->toBe('admin')
        ->and($s['legacy']->roleFor($s['viewer']))->toBeNull();
    $this->getJson('/realtime/summary')->assertOk()
        ->assertJsonPath('meta.backlog_board_id', $replacement->id)->assertJsonPath('meta.backlog_issue_count', 0);
});
