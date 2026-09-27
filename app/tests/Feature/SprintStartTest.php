<?php

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;

function sprintStartBoard(User $owner, Project $project, string $name): Board
{
    $board = Board::create([
        'name' => $name,
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);

    $board->users()->attach($owner);

    return $board;
}

it('starts a sprint through the json modal request', function () {
    Event::fake([BoardUpdated::class]);

    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Sprint start project']);
    $board = sprintStartBoard($owner, $project, 'Sprint 1');
    $endDate = now()->addWeek()->toDateString();

    $this->actingAs($owner)
        ->postJson('/boards/startSprint', [
            'board_id' => $board->id,
            'sprint_goal' => 'Ship the board improvements.',
            'end_date' => $endDate,
        ])
        ->assertOk()
        ->assertJsonPath('event', 'board.updated')
        ->assertJsonPath('payload.entity.status', true)
        ->assertJsonPath(
            'payload.entity.sprint_goal',
            'Ship the board improvements.'
        )
        ->assertJsonPath('payload.entity.end_date', $endDate);

    $board->refresh();

    expect($board->status)->toBeTrue()
        ->and($board->sprint_goal)->toBe('Ship the board improvements.')
        ->and($board->version)->toBe(2);

    Event::assertDispatched(BoardUpdated::class);
});

it('returns a visible validation error when another sprint is active', function () {
    Event::fake([BoardUpdated::class]);

    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Active sprint project']);
    sprintStartBoard($owner, $project, 'Active sprint')->update([
        'status' => true,
    ]);
    $candidate = sprintStartBoard($owner, $project, 'Candidate sprint');

    $this->actingAs($owner)
        ->postJson('/boards/startSprint', [
            'board_id' => $candidate->id,
            'sprint_goal' => 'This should be blocked.',
            'end_date' => now()->addWeek()->toDateString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('board');

    expect($candidate->fresh()->status)->toBeFalse();
});

it('renders an active sprint without a precomputed days-left value', function () {
    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Backlog sprint project']);
    $board = sprintStartBoard($owner, $project, 'Visible active sprint');
    $board->update([
        'status' => true,
        'end_date' => now()->addDays(3)->toDateString(),
    ]);

    $html = Blade::render(
        '<x-sprint-start-details :board="$board" />',
        compact('board')
    );

    expect($html)
        ->toContain('Sprint active')
        ->toContain('3 days left');
});
