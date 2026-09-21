<?php

use App\Models\User;
use Illuminate\Support\Facades\Session;

it('stores criteria separately for each authenticated user and scope', function () {
    $alice = User::factory()->create();
    $bob = User::factory()->create();

    $this->actingAs($alice)
        ->putJson('/task-criteria/backlog', [
            'priority' => 'High',
            'label' => null,
            'sortField' => 'title',
            'sortDirection' => 'asc',
        ])
        ->assertOk()
        ->assertJsonPath('criteria.priority', 'High')
        ->assertJsonPath('criteria.sortField', 'title');

    expect(Session::get(
        "task_criteria.{$alice->id}.backlog.priority"
    ))->toBe('High');

    $this->actingAs($bob)
        ->putJson('/task-criteria/backlog', [
            'priority' => 'Low',
            'label' => 'Backend',
            'sortField' => null,
            'sortDirection' => null,
        ])
        ->assertOk()
        ->assertJsonPath('criteria.priority', 'Low');

    expect(Session::get(
        "task_criteria.{$alice->id}.backlog.priority"
    ))->toBe('High')
        ->and(Session::get(
            "task_criteria.{$bob->id}.backlog.priority"
        ))->toBe('Low');

    $this->deleteJson('/task-criteria/backlog')->assertOk();

    expect(Session::has(
        "task_criteria.{$bob->id}.backlog"
    ))->toBeFalse()
        ->and(Session::get(
            "task_criteria.{$alice->id}.backlog.priority"
        ))->toBe('High');
});

it('rejects arbitrary criteria scopes', function () {
    $this->actingAs(User::factory()->create())
        ->putJson('/task-criteria/not-a-board', [])
        ->assertNotFound();
});
