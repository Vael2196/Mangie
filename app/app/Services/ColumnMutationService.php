<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Column;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ColumnMutationService
{
    public function create(
        Board $board,
        string $name
    ): Column {
        return DB::transaction(function () use ($board, $name) {
            Board::query()
                ->whereKey($board->id)
                ->lockForUpdate()
                ->firstOrFail();

            $position = (int) Column::query()
                ->where('board_id', $board->id)
                ->max('position');

            return Column::create([
                'name' => $name,
                'board_id' => $board->id,
                'position' => $position + 1,
                'color' => 'gray',
            ]);
        }, 3);
    }

    public function rename(
        Column $column,
        string $name
    ): Column {
        $column->update([
            'name' => $name,
            'version' => DB::raw('version + 1'),
        ]);

        return $column->refresh();
    }

    public function recolour(
        Column $column,
        string $color
    ): Column {
        $column->update([
            'color' => $color,
            'version' => DB::raw('version + 1'),
        ]);

        return $column->refresh();
    }

    public function copy(Column $column): Column
    {
        $column->load([
            'tasks' => fn ($query) =>
                $query->orderBy('position'),
            'tasks.users',
        ]);

        return DB::transaction(function () use ($column) {
            Board::query()
                ->whereKey($column->board_id)
                ->lockForUpdate()
                ->firstOrFail();

            Column::query()
                ->where('board_id', $column->board_id)
                ->where('position', '>', $column->position)
                ->increment('position');

            $newColumn = $column->replicate([
                'id',
                'position',
                'version',
                'created_at',
                'updated_at',
            ]);

            $newColumn->name = $column->name . ' copy';
            $newColumn->position = $column->position + 1;
            $newColumn->version = 1;
            $newColumn->save();

            foreach ($column->tasks as $task) {
                $newTask = $task->replicate([
                    'id',
                    'column_id',
                    'position',
                    'version',
                    'created_at',
                    'updated_at',
                ]);

                $newTask->column_id = $newColumn->id;
                $newTask->position = $task->position;
                $newTask->completed_at = null;
                $newTask->version = 1;
                $newTask->save();

                $newTask->users()->sync(
                    $task->users->pluck('id')->all()
                );
            }

            return $newColumn
                ->refresh()
                ->load('tasks.users', 'board');
        }, 3);
    }

    public function delete(Column $column): void
    {
        DB::transaction(function () use ($column) {
            $column = Column::query()
                ->whereKey($column->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($column->tasks()->exists()) {
                throw ValidationException::withMessages([
                    'column' =>
                        'This column cannot be deleted because it contains tasks.',
                ]);
            }

            $boardId = (int) $column->board_id;
            $position = (int) $column->position;

            $column->delete();

            Column::query()
                ->where('board_id', $boardId)
                ->where('position', '>', $position)
                ->decrement('position');
        }, 3);
    }
}
