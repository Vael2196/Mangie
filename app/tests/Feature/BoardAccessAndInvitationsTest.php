<?php

use App\Events\TaskCreated;
use App\Models\Board;
use App\Models\BoardInvitation;
use App\Models\Project;
use App\Models\User;
use App\Notifications\BoardInvitationNotification;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

function accessBoard(?User $owner = null): array
{
    $owner ??= User::factory()->create();
    $project = Project::first() ?? Project::create(['name' => 'Access tests']);
    $board = Board::create(['name' => 'Private board', 'project_id' => $project->id, 'user_id' => $owner->id]);
    $column = $board->columns()->create(['name' => 'TO DO', 'position' => 1]);
    $task = $column->tasks()->create(['title' => 'Private task', 'position' => 1]);

    return compact('owner', 'board', 'column', 'task');
}

function accessInvite(Board $board, ?string $email = null, string $role = 'member'): array
{
    $token = Str::random(64);
    $invitation = $board->invitations()->create([
        'email' => $email, 'role' => $role, 'invited_by' => $board->user_id,
        'token_hash' => hash('sha256', $token), 'expires_at' => now()->addDays(7),
    ]);

    return [$invitation, $token];
}

it('denies outsiders all board task column and realtime reads including board one', function () {
    ['board' => $board, 'column' => $column, 'task' => $task] = accessBoard();
    expect($board->id)->toBe(1);
    $this->actingAs(User::factory()->create());
    foreach ([
        "/boards/{$board->id}", "/boards/{$board->id}/burndownChart", "/boards/{$board->id}/members",
        "/boards/{$board->id}/fragment/home-card", "/columns/{$column->id}/fragment",
        "/tasks/{$task->id}/detail", "/tasks/{$task->id}/fragment/card", "/tasks/{$task->id}/cover",
        "/realtime/entities/board/{$board->id}", "/realtime/entities/task/{$task->id}",
    ] as $url) {
        $this->getJson($url)->assertForbidden();
    }
    $this->get('/backlog/list')->assertOk()->assertDontSeeText('Private task');
    $this->get('/backlog/card')->assertOk()->assertDontSeeText('Private task');
});

it('creates the owner as a verified board admin', function () {
    ['owner' => $owner, 'board' => $board] = accessBoard();
    expect($board->roleFor($owner))->toBe('admin')
        ->and(Gate::forUser($owner)->allows('manageMembers', $board))->toBeTrue();
    $this->actingAs($owner)->get("/boards/{$board->id}/members")->assertOk()
        ->assertSeeText('Invite by name or email')->assertSeeText('Leave board');
});

it('lets observers read details but rejects every board mutation', function () {
    ['board' => $board, 'column' => $column, 'task' => $task] = accessBoard();
    $observer = User::factory()->create();
    $board->users()->attach($observer->id, ['role' => 'observer']);
    $this->actingAs($observer)->get("/boards/{$board->id}")->assertOk()
        ->assertDontSee('data-column-drag-handle', false)->assertDontSee('id="add-column-btn"', false);
    $detail = $this->getJson("/tasks/{$task->id}/detail")->assertOk()->json('html');
    expect($detail)->toContain('data-read-only="true"')->not->toContain('data-save-task');
    $this->postJson('/tasks/store', ['title' => 'No', 'column_id' => $column->id])->assertForbidden();
    $this->patchJson("/columns/{$column->id}/name", ['name' => 'No'])->assertForbidden();
    $this->patchJson("/tasks/{$task->id}/move", ['target_column_id' => $column->id, 'target_position' => 1])->assertForbidden();
    $this->deleteJson("/tasks/{$task->id}")->assertForbidden();
    $this->postJson("/boards/{$board->id}/add-user", ['email' => 'invitee@example.com', 'role' => 'member'])->assertForbidden();
    $this->postJson("/boards/{$board->id}/share-links", ['role' => 'member'])->assertForbidden();
    $this->patchJson("/boards/{$board->id}/columns/order", ['column_ids' => [$column->id], 'expected_version' => 1])->assertForbidden();
});

it('emails an invitation without adding the user and stores only its token hash', function () {
    Notification::fake();
    ['owner' => $owner, 'board' => $board] = accessBoard();
    $invitee = User::factory()->create();
    $this->actingAs($owner)->postJson("/boards/{$board->id}/add-user", ['user_id' => $invitee->id, 'role' => 'observer'])
        ->assertCreated();
    expect($board->roleFor($invitee))->toBeNull();
    $url = null;
    Notification::assertSentOnDemand(BoardInvitationNotification::class, function ($notification) use (&$url) {
        $url = $notification->joinUrl;

        return true;
    });
    $token = basename(parse_url($url, PHP_URL_PATH));
    $invitation = BoardInvitation::sole();
    expect($invitation->token_hash)->toBe(hash('sha256', $token))->not->toBe($token);
    $this->actingAs($invitee)->get($url)->assertOk();
    expect($board->roleFor($invitee))->toBeNull();
    $this->post($url.'/accept')->assertRedirect(route('boards.show', $board));
    expect($board->roleFor($invitee))->toBe('observer');
    $this->get("/boards/{$board->id}")->assertOk();
    $this->post($url.'/accept')->assertStatus(410);
    $this->get($url)->assertRedirect(route('boards.show', $board));
});

it('invites a new email and preserves the join page through registration and verification', function () {
    Notification::fake();
    ['owner' => $owner, 'board' => $board] = accessBoard();
    $this->actingAs($owner)->postJson("/boards/{$board->id}/add-user", [
        'email' => 'newperson@example.com', 'role' => 'member',
    ])->assertCreated();
    expect(User::where('email', 'newperson@example.com')->exists())->toBeFalse();
    [$invitation, $token] = accessInvite($board, 'newperson@example.com');
    auth()->logout();
    $url = route('board-invitations.show', $token);
    $this->get($url)->assertOk()->assertSeeText('Create account')->assertSessionHas('url.intended', $url);
    $user = User::factory()->unverified()->create(['email' => 'newperson@example.com']);
    $this->actingAs($user)->post($url.'/accept')->assertRedirect(route('verification.notice'));
    expect($board->roleFor($user))->toBeNull();
    $user->forceFill(['email_verified_at' => now()])->save();
    $this->post($url.'/accept')->assertRedirect(route('boards.show', $board));
    expect($board->roleFor($user))->toBe('member');
});

it('binds email invitations to the verified recipient and never accepts on GET', function () {
    ['board' => $board] = accessBoard();
    [$invitation, $token] = accessInvite($board, 'recipient@example.com');
    $outsider = User::factory()->create();
    $this->actingAs($outsider)->get(route('board-invitations.show', $token))->assertOk();
    $this->post(route('board-invitations.accept', $token))->assertForbidden();
    expect($board->roleFor($outsider))->toBeNull()->and($invitation->fresh()->accepted_at)->toBeNull();
});

it('expires and revokes invitation links and rejects tampered tokens', function () {
    ['owner' => $owner, 'board' => $board] = accessBoard();
    [$expired, $expiredToken] = accessInvite($board);
    $expired->update(['expires_at' => now()->subMinute()]);
    $this->get(route('board-invitations.show', $expiredToken))->assertStatus(410);
    [$invitation, $token] = accessInvite($board);
    $this->actingAs($owner)->deleteJson("/boards/{$board->id}/invitations/{$invitation->id}")->assertOk();
    $this->post(route('board-invitations.accept', $token))->assertStatus(410);
    $this->get(route('board-invitations.show', Str::random(64)))->assertNotFound();
});

it('allows editors to invite but reserves role changes and removal for admins', function () {
    Notification::fake();
    ['owner' => $owner, 'board' => $board] = accessBoard();
    $member = User::factory()->create();
    $board->users()->attach($member->id, ['role' => 'member']);
    $this->actingAs($member)->postJson("/boards/{$board->id}/add-user", ['email' => 'new@example.com', 'role' => 'member'])->assertCreated();
    $this->patchJson("/boards/{$board->id}/members/{$owner->id}", ['role' => 'observer'])->assertForbidden();
    $this->deleteJson("/boards/{$board->id}/members/{$owner->id}")->assertForbidden();
    $this->postJson("/boards/{$board->id}/share-links", ['role' => 'admin'])->assertUnprocessable();
});

it('preserves a verified admin through demotion removal and leaving', function () {
    ['owner' => $owner, 'board' => $board] = accessBoard();
    $member = User::factory()->create();
    $unverified = User::factory()->unverified()->create();
    $board->users()->attach($member->id, ['role' => 'member']);
    $board->users()->attach($unverified->id, ['role' => 'member']);
    $this->actingAs($owner)->patchJson("/boards/{$board->id}/members/{$owner->id}", ['role' => 'observer'])->assertUnprocessable();
    $this->deleteJson("/boards/{$board->id}/members/{$owner->id}")->assertUnprocessable();
    $this->postJson("/boards/{$board->id}/leave")->assertUnprocessable();
    $this->patchJson("/boards/{$board->id}/members/{$unverified->id}", ['role' => 'admin'])->assertUnprocessable();
    $this->patchJson("/boards/{$board->id}/members/{$member->id}", ['role' => 'admin'])->assertOk();
    $this->patchJson("/boards/{$board->id}/members/{$owner->id}", ['role' => 'member'])->assertOk();
    $this->postJson("/boards/{$board->id}/leave")->assertOk();
    $this->getJson("/boards/{$board->id}")->assertForbidden();
    expect($board->fresh()->user_id)->toBe($owner->id); // no owner-ID bypass
});

it('blocks last-admin account deletion or email changes', function () {
    ['owner' => $owner] = accessBoard();
    $this->actingAs($owner)->patchJson('/profile', ['name' => $owner->name, 'email' => 'changed@example.com'])->assertUnprocessable();
    $this->deleteJson('/profile', ['password' => 'password'])->assertUnprocessable();
    expect($owner->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('removes assignments and blocks old share links for removed users without affecting other invitees', function () {
    ['owner' => $owner, 'board' => $board, 'task' => $task] = accessBoard();
    $member = User::factory()->create();
    $board->users()->attach($member->id);
    $task->users()->attach($member->id);
    [$invitation, $token] = accessInvite($board);
    $this->actingAs($owner)->deleteJson("/boards/{$board->id}/members/{$member->id}")->assertOk();
    expect($task->users()->count())->toBe(0)->and($invitation->fresh()->revoked_at)->toBeNull();
    $this->actingAs($member)->getJson("/tasks/{$task->id}/detail")->assertForbidden();
    $this->post(route('board-invitations.accept', $token))->assertForbidden();
    $anotherViewer = User::factory()->create();
    $this->actingAs($anotherViewer)->post(route('board-invitations.accept', $token))->assertRedirect();
    [$fresh, $freshToken] = accessInvite($board, $member->email);
    $this->actingAs($member)->post(route('board-invitations.accept', $freshToken))->assertRedirect();
    expect($board->roleFor($member))->toBe('member');
});

it('allows share links to join without exposing the board before acceptance or changing existing roles', function () {
    ['owner' => $owner, 'board' => $board] = accessBoard();
    $url = $this->actingAs($owner)->postJson("/boards/{$board->id}/share-links", ['role' => 'observer'])->assertCreated()->json('url');
    $viewer = User::factory()->create();
    $this->actingAs($viewer)->getJson("/boards/{$board->id}")->assertForbidden();
    $this->post($url.'/accept')->assertRedirect();
    expect($board->roleFor($viewer))->toBe('observer');
    $this->actingAs($owner)->post($url.'/accept')->assertRedirect();
    expect($board->roleFor($owner))->toBe('admin');
});

it('scopes suggestions and invitation revocation to the board', function () {
    ['owner' => $owner, 'board' => $board] = accessBoard();
    ['board' => $other] = accessBoard();
    [$invitation] = accessInvite($other);
    $this->actingAs($owner)->deleteJson("/boards/{$board->id}/invitations/{$invitation->id}")->assertNotFound();
    $this->getJson("/boards/{$other->id}/member-suggestions?query=ab")->assertForbidden();
    $this->getJson("/boards/{$board->id}/member-suggestions?query=a")->assertUnprocessable();
});

it('uses per-user content-free realtime invalidations rather than project broadcasts', function () {
    ['owner' => $owner, 'board' => $board, 'task' => $task] = accessBoard();
    $event = new TaskCreated(RealtimePayload::task($task->load('column.board'), $owner), $board->id);
    expect(array_map('strval', $event->broadcastOn()))->toBe(["private-users.{$owner->id}"])
        ->and($event->broadcastWith())->not->toHaveKey('actor')
        ->and($event->broadcastWith()['entity'])->toBe(['id' => $task->id]);
    $this->actingAs($owner)->getJson("/realtime/entities/task/{$task->id}")->assertOk();
    $board->users()->detach($owner->id); // simulate a membership revoked after an event was queued
    $this->getJson("/realtime/entities/task/{$task->id}")->assertForbidden();
});
