<?php

use App\Models\Board;
use App\Models\BoardLabel;
use App\Models\Column;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

function criteriaBoard(
    User $owner,
    Project $project,
    string $name
): array {
    $board = Board::create([
        'name' => $name,
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);
    $board->users()->attach($owner);

    $column = Column::create([
        'name' => $name === 'Product Backlog' ? 'Backlog' : 'TO DO',
        'board_id' => $board->id,
        'position' => 1,
    ]);

    return compact('board', 'column');
}

it('applies priority filtering and sorting to a sprint board', function () {
    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Board criteria project']);
    criteriaBoard($owner, $project, 'Product Backlog');
    ['board' => $board, 'column' => $column] = criteriaBoard(
        $owner,
        $project,
        'Filtered sprint'
    );
    $label = BoardLabel::create([
        'board_id' => $board->id,
        'name' => 'Criteria label',
        'color' => '#3b82f6',
        'position' => 1,
    ]);

    $zulu = Task::create([
        'title' => 'Criteria Zulu',
        'column_id' => $column->id,
        'position' => 1,
        'priority' => 'High',
    ]);
    $alpha = Task::create([
        'title' => 'Criteria Alpha',
        'column_id' => $column->id,
        'position' => 2,
        'priority' => 'High',
    ]);
    Task::create([
        'title' => 'Criteria hidden low',
        'column_id' => $column->id,
        'position' => 3,
        'priority' => 'Low',
    ]);
    Task::create([
        'title' => 'Criteria hidden without label',
        'column_id' => $column->id,
        'position' => 4,
        'priority' => 'High',
    ]);
    $zulu->boardLabels()->attach($label);
    $alpha->boardLabels()->attach($label);

    $this->actingAs($owner)
        ->putJson("/task-criteria/board_{$board->id}", [
            'priority' => 'High',
            'label' => $label->id,
            'sortField' => 'title',
            'sortDirection' => 'asc',
        ])
        ->assertOk();

    $this->get("/boards/{$board->id}")
        ->assertOk()
        ->assertSeeInOrder(['Criteria Alpha', 'Criteria Zulu'])
        ->assertDontSeeText('Criteria hidden low')
        ->assertDontSeeText('Criteria hidden without label');
});

it('applies priority filtering and sorting to the backlog', function () {
    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Backlog criteria project']);
    ['column' => $column] = criteriaBoard(
        $owner,
        $project,
        'Product Backlog'
    );

    Task::create([
        'title' => 'Backlog Zulu',
        'column_id' => $column->id,
        'position' => 1,
        'priority' => 'Medium',
    ]);
    Task::create([
        'title' => 'Backlog Alpha',
        'column_id' => $column->id,
        'position' => 2,
        'priority' => 'Medium',
    ]);
    Task::create([
        'title' => 'Backlog hidden high',
        'column_id' => $column->id,
        'position' => 3,
        'priority' => 'High',
    ]);

    $this->actingAs($owner)
        ->putJson('/task-criteria/backlog', [
            'priority' => 'Medium',
            'label' => null,
            'sortField' => 'title',
            'sortDirection' => 'asc',
        ])
        ->assertOk();

    $this->get('/backlog/list')
        ->assertOk()
        ->assertSeeInOrder(['Backlog Alpha', 'Backlog Zulu'])
        ->assertDontSeeText('Backlog hidden high');
});
