<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STANDARD_LABELS = [
        ['name' => 'API', 'color' => '#3b82f6'],
        ['name' => 'Backend', 'color' => '#8b5cf6'],
        ['name' => 'Frontend', 'color' => '#14b8a6'],
        ['name' => 'UI/UX', 'color' => '#ec4899'],
        ['name' => 'Database', 'color' => '#f97316'],
    ];

    public function up(): void
    {
        Schema::create('board_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 7);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['board_id', 'name']);
        });

        Schema::create('board_label_task', function (Blueprint $table) {
            $table->foreignId('board_label_id')
                ->constrained('board_labels')
                ->cascadeOnDelete();
            $table->foreignId('task_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->primary(['board_label_id', 'task_id']);
        });

        Schema::create('label_libraries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 80);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('label_library_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('label_library_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('color', 7);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        $now = now();

        DB::table('boards')
            ->orderBy('id')
            ->each(function ($board) use ($now) {
                foreach (self::STANDARD_LABELS as $index => $label) {
                    DB::table('board_labels')->insert([
                        'board_id' => $board->id,
                        'name' => $label['name'],
                        'color' => $label['color'],
                        'position' => $index + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });

        DB::table('tasks')
            ->join('columns', 'columns.id', '=', 'tasks.column_id')
            ->whereNotNull('tasks.labels')
            ->where('tasks.labels', '!=', '')
            ->select([
                'tasks.id as task_id',
                'tasks.labels as label_name',
                'columns.board_id',
            ])
            ->orderBy('tasks.id')
            ->each(function ($task) {
                $labelId = DB::table('board_labels')
                    ->where('board_id', $task->board_id)
                    ->where('name', $task->label_name)
                    ->value('id');

                if ($labelId) {
                    DB::table('board_label_task')->insertOrIgnore([
                        'board_label_id' => $labelId,
                        'task_id' => $task->task_id,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('label_library_items');
        Schema::dropIfExists('label_libraries');
        Schema::dropIfExists('board_label_task');
        Schema::dropIfExists('board_labels');
    }
};
