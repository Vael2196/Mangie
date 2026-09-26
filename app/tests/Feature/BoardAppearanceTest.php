<?php

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

function appearanceBoard(User $owner, Project $project, string $name): Board
{
    $board = Board::create([
        'name' => $name,
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);

    $board->users()->attach($owner);

    return $board;
}

test('board colour is isolated to the selected board', function () {
    Event::fake([BoardUpdated::class]);

    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Appearance project']);
    $target = appearanceBoard($owner, $project, 'Target board');
    $other = appearanceBoard($owner, $project, 'Other board');

    $this
        ->actingAs($owner)
        ->patchJson("/boards/{$target->id}/appearance", [
            'mode' => 'color',
            'background_color' => '#dbeafe',
        ])
        ->assertOk()
        ->assertJsonPath('event', 'board.updated')
        ->assertJsonPath('payload.entity.background_color', '#dbeafe')
        ->assertJsonPath('payload.meta.appearance_changed', true);

    expect($target->fresh()->background_color)->toBe('#dbeafe')
        ->and($target->fresh()->version)->toBe(2)
        ->and($other->fresh()->background_color)->toBe('#eef2ff');

    Event::assertDispatched(BoardUpdated::class);
});

test('board photo replaces the previous photo without affecting other boards', function () {
    Event::fake([BoardUpdated::class]);
    Storage::fake('public');

    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Photo project']);
    $target = appearanceBoard($owner, $project, 'Photo board');
    $other = appearanceBoard($owner, $project, 'Untouched board');

    $png = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwC'
        . 'AAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
    );

    $photo = UploadedFile::fake()->createWithContent(
        'background.png',
        $png
    );

    $this
        ->actingAs($owner)
        ->post("/boards/{$target->id}/appearance", [
            '_method' => 'PATCH',
            'mode' => 'image',
            'background_image' => $photo,
        ], ['Accept' => 'application/json'])
        ->assertOk();

    $path = $target->fresh()->background_image_path;

    expect($path)->not->toBeNull()
        ->and($other->fresh()->background_image_path)->toBeNull();

    Storage::disk('public')->assertExists($path);

    $this
        ->actingAs($owner)
        ->patchJson("/boards/{$target->id}/appearance", [
            'mode' => 'default',
        ])
        ->assertOk();

    expect($target->fresh()->background_image_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
