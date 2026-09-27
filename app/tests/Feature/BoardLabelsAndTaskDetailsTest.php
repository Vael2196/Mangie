<?php

use AppEventsBoardUpdated;
use AppEventsTaskUpdated;
use AppModelsBoard;
use AppModelsBoardLabel;
use AppModelsColumn;
use AppModelsProject;
use AppModelsTask;
use AppModelsUser;
use IlluminateSupportFacadesEvent;

function labelFeatureBoard(User $owner, string $name): array
{
    $project = Project::first()
        ?? Project::create(['name' => 'Labels Project']);

    $board = Board::create([
        'name' => $name,
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);
    $board->users()->attach($owner);

    $column = Column::create([
        'name' => 'TO DO',
        'board_id' => $board->id,
        'position' => 1,
    ]);

    return compact('board', 'column');
}

it('keeps custom labels board specific and reuses them through a saved list', function () {
    Event::fake([BoardUpdated::class]);

    $owner = User::factory()->create();
    ['board' => $source] = labelFeatureBoard($owner, 'Source board');
    ['board' => $target] = labelFeatureBoard($owner, 'Target board');

    $created = $this->actingAs($owner)
        ->postJson("/boards/{$source->id}/labels", [
            'name' => 'Research',
            'color' => '#0ea5e9',
        ])
        ->assertCreated()
        ->assertJsonPath('label.name', 'Research')
        ->assertJsonPath('label.color', '#0ea5e9');

    expect($target->labels()->where('name', 'Research')->exists())
        ->toBeFalse();

    $libraryId = $this->postJson(
        "/boards/{$source->id}/label-libraries",
        ['name' => 'Discovery labels']
    )
        ->assertCreated()
        ->assertJsonPath('library.name', 'Discovery labels')
        ->json('library.id');

    $this->postJson("/boards/{$target->id}/labels/import", [
        'source' => 'library',
        'library_id' => $libraryId,
    ])
        ->assertOk()
        ->assertJsonFragment([
            'name' => 'Research',
            'color' => '#0ea5e9',
        ]);

    expect($target->labels()->where('name', 'Research')->exists())
        ->toBeTrue();

    $this->deleteJson("/label-libraries/{$libraryId}")
        ->assertOk();

    $this->assertDatabaseMissing('label_libraries', [
        'id' => $libraryId,
    ]);
    $this->assertDatabaseHas('board_labels', [
        'board_id' => $target->id,
        'name' => 'Research',
    ]);

    Event::assertDispatched(BoardUpdated::class);
    expect($created->json('label.id'))->toBeInt();
});

it('saves task labels dates sections and checklists in one versioned update', function () {
    Event::fake([TaskUpdated::class]);

    $owner = User::factory()->create();
    ['board' => $board, 'column' => $column] = labelFeatureBoard(
        $owner,
        'Detailed board'
    );
    $label = BoardLabel::create([
        'board_id' => $board->id,
        'name' => 'Needs review',
        'color' => '#f97316',
        'position' => 1,
    ]);
    $task = Task::create([
        'title' => 'Draft task',
        'column_id' => $column->id,
        'position' => 1,
    ]);

    $this->actingAs($owner)
        ->patchJson("/tasks/{$task->id}", [
            'column_id' => $column->id,
            'title' => 'Detailed task',
            'description' => 'Everything in one save.',
            'assignee' => null,
            'label_ids' => [$label->id],
            'priority' => 'High',
            'storyPoint' => 5,
            'timeLog' => 2,
            'start_at' => '2026-09-28T09:00',
            'due_at' => '2026-10-02T17:00',
            'due_complete' => true,
            'sections' => [[
                'title' => 'Acceptance notes',
                'content' => 'Confirm the happy path.',
            ]],
            'checklists' => [[
                'title' => 'Release checklist',
                'items' => [
                    [
                        'content' => 'Write tests',
                        'is_complete' => true,
                    ],
                    [
                        'content' => 'Deploy',
                        'is_complete' => false,
                    ],
                ],
            ]],
            'expected_version' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('payload.entity.version', 2)
        ->assertJsonPath('payload.entity.label_ids.0', $label->id)
        ->assertJsonPath('payload.entity.section_count', 1)
        ->assertJsonPath('payload.entity.checklist_total', 2)
        ->assertJsonPath('payload.entity.checklist_complete', 1)
        ->assertJsonPath('payload.entity.due_complete', true);

    $task->refresh();

    expect($task->version)->toBe(2)
        ->and($task->boardLabels()->pluck('board_labels.id')->all())
        ->toBe([$label->id])
        ->and($task->sections()->count())->toBe(1)
        ->and($task->checklists()->count())->toBe(1)
        ->and($task->checklists()->first()->items()->count())->toBe(2)
        ->and($task->due_complete)->toBeTrue();

    Event::assertDispatched(TaskUpdated::class);
});

it('rejects labels that belong to another board', function () {
    $owner = User::factory()->create();
    ['board' => $source] = labelFeatureBoard($owner, 'Other board');
    ['column' => $column] = labelFeatureBoard($owner, 'Current board');
    $foreignLabel = BoardLabel::create([
        'board_id' => $source->id,
        'name' => 'Foreign',
        'color' => '#ef4444',
        'position' => 1,
    ]);
    $task = Task::create([
        'title' => 'Protected task',
        'column_id' => $column->id,
        'position' => 1,
    ]);

    $this->actingAs($owner)
        ->patchJson("/tasks/{$task->id}", [
            'column_id' => $column->id,
            'title' => $task->title,
            'description' => null,
            'assignee' => null,
            'label_ids' => [$foreignLabel->id],
            'priority' => null,
            'storyPoint' => 0,
            'timeLog' => 0,
            'expected_version' => 1,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('label_ids');
});

it('maps labels by name when a task moves to another board', function () {
    $owner = User::factory()->create();
    ['board' => $source, 'column' => $sourceColumn] = labelFeatureBoard(
        $owner,
        'Move source'
    );
    ['board' => $target, 'column' => $targetColumn] = labelFeatureBoard(
        $owner,
        'Move target'
    );

    $sourceLabel = BoardLabel::create([
        'board_id' => $source->id,
        'name' => 'Ready',
        'color' => '#22c55e',
        'position' => 1,
    ]);
    $targetLabel = BoardLabel::create([
        'board_id' => $target->id,
        'name' => 'Ready',
        'color' => '#14b8a6',
        'position' => 1,
    ]);
    $task = Task::create([
        'title' => 'Move with label',
        'column_id' => $sourceColumn->id,
        'position' => 1,
        'labels' => 'Ready',
    ]);
    $task->boardLabels()->attach($sourceLabel);

    $this->actingAs($owner)
        ->patchJson("/tasks/{$task->id}/move", [
            'target_column_id' => $targetColumn->id,
            'target_position' => 1,
        ])
        ->assertOk()
        ->assertJsonPath('payload.entity.board_id', $target->id)
        ->assertJsonPath('payload.entity.label_ids.0', $targetLabel->id);

    expect($task->fresh()->boardLabels()->pluck('board_labels.id')->all())
        ->toBe([$targetLabel->id]);
});
