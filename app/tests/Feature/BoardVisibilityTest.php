<?php

use App\Models\Board;
use App\Models\Project;
use App\Models\User;

function visibilityBoard(
    User $owner,
    Project $project,
    string $name,
    bool $active = false,
    bool $completed = false
): Board {
    $board = Board::create([
        'name' => $name,
        'project_id' => $project->id,
        'user_id' => $owner->id,
        'status' => $active,
        'completed' => $completed,
        'end_date' => $active
            ? now()->addWeek()->toDateString()
            : null,
    ]);

    $board->users()->attach($owner);

    return $board;
}

function visibilityScenario(): array
{
    $viewer = User::factory()->create();
    $otherOwner = User::factory()->create();
    $outsider = User::factory()->create();
    $project = Project::create(['name' => 'Visibility project']);

    $backlog = visibilityBoard($viewer, $project, 'Product Backlog');
    $ownedActive = visibilityBoard(
        $viewer,
        $project,
        'Owned active sprint',
        true
    );
    $ownedInactive = visibilityBoard(
        $viewer,
        $project,
        'Owned inactive sprint'
    );
    $sharedActive = visibilityBoard(
        $otherOwner,
        $project,
        'Shared active sprint',
        true
    );
    $sharedInactive = visibilityBoard(
        $otherOwner,
        $project,
        'Shared inactive sprint'
    );
    $sharedCompleted = visibilityBoard(
        $otherOwner,
        $project,
        'Shared completed sprint',
        false,
        true
    );
    $outsideBoard = visibilityBoard(
        $outsider,
        $project,
        'Private outsider sprint',
        true
    );

    $sharedActive->users()->attach($viewer);
    $sharedInactive->users()->attach($viewer);
    $sharedCompleted->users()->attach($viewer);

    return compact(
        'viewer',
        'backlog',
        'ownedActive',
        'ownedInactive',
        'sharedActive',
        'sharedInactive',
        'sharedCompleted',
        'outsideBoard'
    );
}

it('shows owned and shared active sprints on home by default', function () {
    $scenario = visibilityScenario();

    $this->actingAs($scenario['viewer'])
        ->get('/home')
        ->assertOk()
        ->assertSeeText('Owned active sprint')
        ->assertSeeText('Shared active sprint')
        ->assertDontSeeText('Owned inactive sprint')
        ->assertDontSeeText('Shared inactive sprint')
        ->assertDontSeeText('Shared completed sprint')
        ->assertDontSeeText('Private outsider sprint')
        ->assertDontSee("sprint-card-{$scenario['backlog']->id}", false);
});

it('filters home to shared sprints and can include inactive ones', function () {
    $scenario = visibilityScenario();

    $this->actingAs($scenario['viewer'])
        ->get('/home?filters=1&show_inactive=1&ownership[]=shared')
        ->assertOk()
        ->assertSeeText('Shared active sprint')
        ->assertSeeText('Shared inactive sprint')
        ->assertSeeText('Shared completed sprint')
        ->assertDontSeeText('Owned active sprint')
        ->assertDontSeeText('Owned inactive sprint')
        ->assertDontSeeText('Private outsider sprint');
});

it('shows all accessible sprints on the dashboard by default', function () {
    $scenario = visibilityScenario();

    $this->actingAs($scenario['viewer'])
        ->get('/dashboard')
        ->assertOk()
        ->assertSeeText('Owned active sprint')
        ->assertSeeText('Owned inactive sprint')
        ->assertSeeText('Shared active sprint')
        ->assertSeeText('Shared inactive sprint')
        ->assertSeeText('Shared completed sprint')
        ->assertDontSeeText('Private outsider sprint')
        ->assertDontSee("sprint-card-{$scenario['backlog']->id}", false);
});
