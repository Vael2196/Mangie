<?php

namespace App\Http\Controllers;

use App\Events\TaskUpdated;
use App\Models\Task;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class TaskCoverController extends Controller
{
    public function show(Task $task)
    {
        Gate::authorize('view', $task->column->board);
        abort_unless($task->cover_image_path && Storage::disk('local')->exists($task->cover_image_path), 404);

        return Storage::disk('local')->response($task->cover_image_path, null, [
            'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request, Task $task)
    {
        Gate::authorize('update', $task->column->board);
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192', 'dimensions:max_width=12000,max_height=12000'],
            'expected_version' => ['required', 'integer', 'min:1'],
        ]);
        $path = $request->file('image')->store('task-covers', 'local');
        abort_unless($path, 500, 'The photo could not be stored.');
        try {
            return $this->change($request, $task, $path);
        } catch (\Throwable $exception) {
            if ($task->fresh()?->cover_image_path !== $path) {
                Storage::disk('local')->delete($path);
            }
            throw $exception;
        }
    }

    public function destroy(Request $request, Task $task)
    {
        Gate::authorize('update', $task->column->board);
        $request->validate(['expected_version' => ['required', 'integer', 'min:1']]);

        return $this->change($request, $task, null);
    }

    private function change(Request $request, Task $task, ?string $path)
    {
        [$task, $oldPath] = DB::transaction(function () use ($request, $task, $path) {
            $task = Task::with('column.board')->whereKey($task->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('update', $task->column->board);
            abort_unless((int) $task->version === $request->integer('expected_version'), 409,
                'The task changed elsewhere. Reopen it before updating the cover.');
            $oldPath = $task->cover_image_path;
            $task->forceFill(['cover_image_path' => $path, 'version' => (int) $task->version + 1])->save();

            app(\App\Services\TaskWatchService::class)->record($task);

            return [$task->refresh()->load('column.board', 'users'), $oldPath];
        }, 3);
        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }
        $event = new TaskUpdated(RealtimePayload::task($task, $request->user()), [(int) $task->column->board_id]);
        broadcast($event)->toOthers();

        return response()->json($event->response());
    }
}
