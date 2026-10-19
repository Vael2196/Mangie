<?php

use App\Models\Board;
use App\Models\Project;
use App\Models\TaskWatch;
use App\Models\User;
use App\Services\PresenceService;
use Illuminate\Support\Facades\Storage;

function presenceFixture(): array
{
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $observer = User::factory()->create();
    $project = Project::create(['name' => 'Presence project']);
    $board = Board::create(['name' => 'Presence board', 'user_id' => $owner->id, 'project_id' => $project->id]);
    $board->users()->attach([$member->id => ['role' => 'member'], $observer->id => ['role' => 'observer']]);
    $column = $board->columns()->create(['name' => 'TO DO', 'position' => 1]);
    $task = $column->tasks()->create(['title' => 'Watched task', 'position' => 1]);

    return compact('owner', 'member', 'observer', 'board', 'task');
}

it('defaults to three hour emails and saves settings only for the authenticated user', function () {
    ['owner' => $owner, 'member' => $member] = presenceFixture();
    expect($owner->fresh()->notification_frequency)->toBe('3_hours');
    $this->actingAs($owner)->get('/settings')->assertOk()->assertSee('Task update emails');
    $this->patch('/settings', ['notification_frequency' => '30_minutes', 'invisible' => 1])->assertRedirect();
    expect($owner->fresh()->notification_frequency)->toBe('30_minutes')->and($owner->fresh()->invisible)->toBeTrue()
        ->and($member->fresh()->notification_frequency)->toBe('3_hours')->and($member->fresh()->invisible)->toBeFalse();
    $this->patchJson('/settings', ['notification_frequency' => 'invalid', 'invisible' => false])->assertUnprocessable();
});

it('clears pending email for never without removing the watch subscription', function () {
    ['owner' => $owner, 'task' => $task] = presenceFixture();
    $watch = TaskWatch::create(['user_id' => $owner->id, 'task_id' => $task->id, 'pending_since' => now(), 'change_count' => 2]);
    $this->actingAs($owner)->patch('/settings', ['notification_frequency' => 'never', 'invisible' => 0])->assertRedirect();
    expect($watch->fresh()->pending_since)->toBeNull()->and($watch->fresh()->change_count)->toBe(0);
});

it('shows online activity and last seen time but hides invisible users timestamps', function () {
    ['owner' => $owner, 'member' => $member, 'board' => $board] = presenceFixture();
    $member->forceFill(['last_active_at' => now()->subMinutes(20)])->save();
    $this->actingAs($owner)->getJson("/boards/{$board->id}/presence")->assertOk()->assertJsonFragment(['id' => $member->id, 'status' => 'online']);
    $member->forceFill(['last_active_at' => now()->subHours(3)])->save();
    $status = collect($this->getJson("/boards/{$board->id}/presence")->assertOk()->json('users'))->firstWhere('id', $member->id);
    expect($status['status'])->toBe('offline')->and($status['label'])->toContain('3 hours')->and($status['last_seen_at'])->not->toBeNull();
    $member->forceFill(['invisible' => true, 'last_active_at' => now()])->save();
    $this->getJson("/boards/{$board->id}/presence")->assertOk()->assertJsonFragment(['id' => $member->id, 'status' => 'offline', 'label' => 'Offline', 'last_seen_at' => null]);
});

it('denies observer and outsider presence reads including hidden fields on user payloads', function () {
    ['owner' => $owner, 'observer' => $observer, 'board' => $board] = presenceFixture();
    $this->actingAs($observer)->getJson("/boards/{$board->id}/presence")->assertForbidden();
    $html = $this->get("/boards/{$board->id}")->assertOk()->getContent();
    expect($html)->not->toContain('data-presence-board=');
    $this->getJson("/realtime/entities/user/{$owner->id}")->assertOk()->assertJsonMissingPath('payload.entity.last_active_at')->assertJsonMissingPath('payload.entity.invisible');
    $this->actingAs(User::factory()->create())->getJson("/boards/{$board->id}/presence")->assertForbidden();
    expect($owner->fresh()->toArray())->not->toHaveKeys(['last_active_at', 'invisible', 'notification_frequency']);
});

it('records real activity but passive polling does not keep a user online', function () {
    ['owner' => $owner, 'board' => $board] = presenceFixture();
    $previous = now()->subHours(2)->startOfSecond();
    $owner->forceFill(['last_active_at' => $previous])->save();
    $this->actingAs($owner)->getJson("/boards/{$board->id}/presence")->assertOk();
    expect($owner->fresh()->last_active_at->equalTo($previous))->toBeTrue();
    $this->getJson('/presence/me')->assertOk();
    expect($owner->fresh()->last_active_at->equalTo($previous))->toBeTrue();
    $this->postJson('/activity')->assertNoContent();
    expect($owner->fresh()->last_active_at->gt($previous))->toBeTrue();
});

it('records sign in and changes last seen labels from hours to days', function () {
    $user = User::factory()->create(['password' => 'correct-password']);
    $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])->assertRedirect();
    expect($user->fresh()->last_active_at)->not->toBeNull();
    $user->forceFill(['last_active_at' => now()->subDays(2)])->save();
    expect(app(PresenceService::class)->forUser($user)['label'])->toContain('2 days');
});

it('does not count automatic cover image loading as activity', function () {
    Storage::fake('local');
    ['owner' => $owner, 'task' => $task] = presenceFixture();
    $previous = now()->subHours(2)->startOfSecond();
    $owner->forceFill(['last_active_at' => $previous])->save();
    Storage::disk('local')->put('task-covers/test.png', 'test image');
    $task->forceFill(['cover_image_path' => 'task-covers/test.png'])->save();
    $this->actingAs($owner)->get("/tasks/{$task->id}/cover")->assertOk();
    expect($owner->fresh()->last_active_at->equalTo($previous))->toBeTrue();
});
