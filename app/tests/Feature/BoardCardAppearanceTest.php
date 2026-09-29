<?php

use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;

function cardAppearanceBoard(
    User $owner,
    Project $project,
    string $name,
    array $attributes = []
): Board {
    $board = Board::create([
        'name' => $name,
        'project_id' => $project->id,
        'user_id' => $owner->id,
        ...$attributes,
    ]);

    $board->users()->attach($owner);

    return $board;
}

test('default sprint card remains plain and omits the old helper copy', function () {
    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Card project']);
    $board = cardAppearanceBoard($owner, $project, 'Default card');

    $html = $this->actingAs($owner)->blade(
        '<x-show-sprint-board :board="$board" />',
        compact('board')
    );

    $html
        ->assertSee('data-board-card-background="default"', false)
        ->assertDontSee('Open this sprint to manage tasks');
});

test('custom sprint colour is rendered on its dashboard card', function () {
    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Colour card project']);
    $board = cardAppearanceBoard(
        $owner,
        $project,
        'Colour card',
        ['background_color' => '#0f766e']
    );

    $html = Blade::render(
        '<x-show-sprint-board :board="$board" />',
        compact('board')
    );

    expect($html)
        ->toContain('data-board-card-background="colour"')
        ->toContain('background-color: #0f766e;');
});

test('custom sprint photo is rendered on its dashboard card', function () {
    Storage::fake('public');

    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Photo card project']);
    $path = 'board-backgrounds/card-background.png';
    Storage::disk('public')->put($path, 'image-bytes');
    $board = cardAppearanceBoard(
        $owner,
        $project,
        'Photo card',
        ['background_image_path' => $path]
    );

    $html = Blade::render(
        '<x-show-sprint-board :board="$board" />',
        compact('board')
    );

    expect($html)
        ->toContain('data-board-card-background="image"')
        ->toContain('/storage/board-backgrounds/card-background.png');
});
