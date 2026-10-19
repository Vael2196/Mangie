<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskActionService
{
    public function change(Task $task, array $data): Task
    {
        return DB::transaction(function () use ($task, $data) {
            $task = Task::with('column.board')->lockForUpdate()->findOrFail($task->id);
            Gate::authorize('update', $task->column->board);
            abort_unless((int) $task->version === (int) $data['expected_version'], 409,
                'This task changed elsewhere. Reopen the menu and try again.');
            if (array_key_exists('completed', $data)) {
                $task->completed_at = $data['completed'] ? ($task->completed_at ?? now()) : null;
            }
            if (array_key_exists('color', $data)) {
                $task->color = $data['color'];
            }
            if (array_key_exists('joined', $data)) {
                if ($data['joined']) {
                    $task->users()->syncWithoutDetaching([auth()->id()]);
                } else {
                    $task->users()->detach(auth()->id());
                }
            }
            $task->version = (int) $task->version + 1;
            $task->save();
            app(TaskWatchService::class)->record($task);

            return $task->refresh()->load('column.board', 'users');
        }, 3);
    }

    public function copy(Task $source, int $expectedVersion): Task
    {
        $newCover = null;
        try {
            return DB::transaction(function () use ($source, $expectedVersion, &$newCover) {
                $source = Task::with('column.board', 'users', 'boardLabels', 'sections', 'checklists.items')
                    ->lockForUpdate()->findOrFail($source->id);
                Gate::authorize('update', $source->column->board);
                abort_unless((int) $source->version === $expectedVersion, 409,
                    'This task changed elsewhere. Reopen the menu and try again.');
                $source->column()->lockForUpdate()->firstOrFail();
                $copy = $source->replicate(['created_at', 'updated_at', 'version', 'cover_image_path']);
                $copy->title = Str::limit($source->title, 250, '').' copy';
                $copy->position = (int) Task::where('column_id', $source->column_id)->max('position') + 1;
                $copy->version = 1;
                if ($source->cover_image_path) {
                    $newCover = 'task-covers/'.Str::uuid().'.'.pathinfo($source->cover_image_path, PATHINFO_EXTENSION);
                    abort_unless(Storage::disk('local')->copy($source->cover_image_path, $newCover), 500,
                        'The cover photo could not be copied.');
                    $copy->cover_image_path = $newCover;
                }
                $copy->save();
                $eligible = $source->column->board->users()->whereIn('board_user.role', ['admin', 'member'])
                    ->whereNotNull('users.email_verified_at')->pluck('users.id');
                $copy->users()->sync($source->users->pluck('id')->intersect($eligible)->all());
                $copy->boardLabels()->sync($source->boardLabels->pluck('id')->all());
                foreach ($source->sections as $section) {
                    $copy->sections()->create($section->only(['title', 'content', 'position']));
                }
                foreach ($source->checklists as $checklist) {
                    $newChecklist = $copy->checklists()->create($checklist->only(['title', 'position']));
                    foreach ($checklist->items as $item) {
                        $newChecklist->items()->create($item->only(['content', 'is_complete', 'completed_at', 'position']));
                    }
                }

                return $copy->refresh()->load('column.board', 'users');
            }, 1); // File copies must not be repeated by an automatic transaction retry.
        } catch (\Throwable $exception) {
            if ($newCover) {
                Storage::disk('local')->delete($newCover);
            }
            throw $exception;
        }
    }
}
