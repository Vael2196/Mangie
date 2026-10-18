<?php

use App\Events\ColumnsReordered;
use App\Events\TaskUpdated;
use App\Models\Board;
use App\Models\Column;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

function coverBoard(): array
{
    $owner = User::factory()->create();
    $project = Project::create(['name' => 'Cover project']);
    $board = Board::create(['name' => 'Picture board', 'project_id' => $project->id, 'user_id' => $owner->id]);
    $columns = collect(['TO DO', 'DOING', 'DONE'])->map(fn ($name, $i) => $board->columns()->create([
        'name' => $name, 'position' => $i + 1,
    ]));
    $task = $columns[0]->tasks()->create(['title' => 'Task with cover', 'description' => 'Keep these details', 'position' => 1]);

    return compact('owner', 'board', 'columns', 'task');
}

it('stores versioned task covers privately and renders them in cards and details', function () {
    Storage::fake('local');
    Event::fake([TaskUpdated::class]);
    ['owner' => $owner, 'task' => $task] = coverBoard();
    $this->actingAs($owner)->post("/tasks/{$task->id}/cover", [
        'image' => UploadedFile::fake()->image('cover.png', 500, 300), 'expected_version' => 1,
    ])->assertOk()->assertJsonPath('event', 'task.updated')->assertJsonPath('payload.entity.version', 2);
    $path = $task->fresh()->cover_image_path;
    Storage::disk('local')->assertExists($path);
    expect($path)->toStartWith('task-covers/')->and($task->fresh()->cover_image_url)->toContain("/tasks/{$task->id}/cover");
    $this->get("/tasks/{$task->id}/cover")->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($this->getJson("/tasks/{$task->id}/fragment/card")->assertOk()->json('html'))->toContain('alt="Task cover"');
    expect($this->getJson("/tasks/{$task->id}/detail")->assertOk()->json('html'))->toContain('data-task-cover-image');
    $this->deleteJson("/tasks/{$task->id}/cover", ['expected_version' => 2])->assertOk()->assertJsonPath('payload.entity.version', 3);
    Storage::disk('local')->assertMissing($path);
    expect($task->fresh()->cover_image_path)->toBeNull();
});

it('replaces covers without leaving the previous file and rejects stale uploads', function () {
    Storage::fake('local');
    Event::fake([TaskUpdated::class]);
    ['owner' => $owner, 'task' => $task] = coverBoard();
    $this->actingAs($owner)->postJson("/tasks/{$task->id}/cover", [
        'image' => UploadedFile::fake()->image('old.jpg'), 'expected_version' => 1,
    ])->assertOk();
    $oldPath = $task->fresh()->cover_image_path;
    $this->postJson("/tasks/{$task->id}/cover", [
        'image' => UploadedFile::fake()->image('new.jpg'), 'expected_version' => 2,
    ])->assertOk();
    Storage::disk('local')->assertMissing($oldPath);
    $newPath = $task->fresh()->cover_image_path;
    $this->postJson("/tasks/{$task->id}/cover", [
        'image' => UploadedFile::fake()->image('stale.jpg'), 'expected_version' => 1,
    ])->assertConflict();
    expect(Storage::disk('local')->allFiles('task-covers'))->toBe([$newPath]);
    expect($task->fresh()->description)->toBe('Keep these details');
});

it('denies cover access to outsiders and cover mutations to observers', function () {
    Storage::fake('local');
    ['owner' => $owner, 'board' => $board, 'task' => $task] = coverBoard();
    $outsider = User::factory()->create();
    $observer = User::factory()->create();
    $board->users()->attach($observer->id, ['role' => 'observer']);
    $this->actingAs($owner)->postJson("/tasks/{$task->id}/cover", [
        'image' => UploadedFile::fake()->image('cover.jpg'), 'expected_version' => 1,
    ])->assertOk();
    $this->actingAs($outsider)->get("/tasks/{$task->id}/cover")->assertForbidden();
    $this->actingAs($observer)->get("/tasks/{$task->id}/cover")->assertOk();
    $this->deleteJson("/tasks/{$task->id}/cover", ['expected_version' => 2])->assertForbidden();
    $this->postJson("/tasks/{$task->id}/cover", ['image' => UploadedFile::fake()->image('no.jpg'), 'expected_version' => 2])->assertForbidden();
});

it('rejects non-image covers', function () {
    Storage::fake('local');
    ['owner' => $owner, 'task' => $task] = coverBoard();
    $this->actingAs($owner)->postJson("/tasks/{$task->id}/cover", [
        'image' => UploadedFile::fake()->create('script.php', 2, 'text/x-php'), 'expected_version' => 1,
    ])->assertUnprocessable()->assertJsonValidationErrors('image');
    expect(Storage::disk('local')->allFiles())->toBe([]);
});

it('reorders entire columns without changing their tasks or properties', function () {
    Event::fake([ColumnsReordered::class]);
    ['owner' => $owner, 'board' => $board, 'columns' => $columns, 'task' => $task] = coverBoard();
    $columns[0]->update(['color' => 'purple']);
    $ids = $columns->reverse()->pluck('id')->values()->all();
    $this->actingAs($owner)->patchJson("/boards/{$board->id}/columns/order", [
        'column_ids' => $ids, 'expected_version' => 1,
    ])->assertOk()->assertJsonPath('event', 'columns.reordered')
        ->assertJsonPath('payload.meta.column_order', $ids)->assertJsonPath('payload.entity.version', 2);
    expect($board->columns()->orderBy('position')->pluck('id')->all())->toBe($ids)
        ->and($task->fresh()->column_id)->toBe($columns[0]->id)
        ->and($task->fresh()->description)->toBe('Keep these details')
        ->and($columns[0]->fresh()->color)->toBe('purple')
        ->and($columns[0]->fresh()->position)->toBe(3);
    Event::assertDispatched(ColumnsReordered::class);
});

it('rejects incomplete duplicate and foreign column orders without partial writes', function () {
    ['owner' => $owner, 'board' => $board, 'columns' => $columns] = coverBoard();
    $foreign = Column::create(['name' => 'Foreign', 'board_id' => Board::create([
        'name' => 'Other', 'project_id' => $board->project_id, 'user_id' => $owner->id,
    ])->id, 'position' => 1]);
    $this->actingAs($owner);
    foreach ([[$columns[0]->id], [$columns[0]->id, $columns[0]->id, $columns[1]->id],
        [$columns[0]->id, $columns[1]->id, $foreign->id]] as $ids) {
        $this->patchJson("/boards/{$board->id}/columns/order", ['column_ids' => $ids, 'expected_version' => 1])->assertUnprocessable();
    }
    expect($board->columns()->orderBy('position')->pluck('id')->all())->toBe($columns->pluck('id')->all())
        ->and($board->fresh()->version)->toBe(1);
});

it('rejects a stale reorder instead of overwriting a newer order', function () {
    ['owner' => $owner, 'board' => $board, 'columns' => $columns] = coverBoard();
    $ids = $columns->reverse()->pluck('id')->values()->all();
    $this->actingAs($owner)->patchJson("/boards/{$board->id}/columns/order", ['column_ids' => $ids, 'expected_version' => 1])->assertOk();
    $this->patchJson("/boards/{$board->id}/columns/order", ['column_ids' => $columns->pluck('id')->all(), 'expected_version' => 1])->assertConflict();
    expect($board->columns()->orderBy('position')->pluck('id')->all())->toBe($ids);
});

it('allows reordering after an unrelated board update but rejects an outdated order', function () {
    ['owner' => $owner, 'board' => $board, 'columns' => $columns] = coverBoard();
    $original = $columns->pluck('id')->all();
    $reversed = array_reverse($original);
    $board->update(['background_color' => '#ccfbf1', 'version' => 2]);
    $this->actingAs($owner)->patchJson("/boards/{$board->id}/columns/order", [
        'column_ids' => $reversed, 'expected_version' => 1, 'expected_order' => $original,
    ])->assertOk();
    $this->patchJson("/boards/{$board->id}/columns/order", [
        'column_ids' => $original, 'expected_version' => 1, 'expected_order' => $original,
    ])->assertConflict();
    expect($board->columns()->orderBy('position')->pluck('id')->all())->toBe($reversed);
});
