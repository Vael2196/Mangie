<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dateTime('start_at')->nullable()->after('completed_at');
            $table->dateTime('due_at')->nullable()->after('start_at');
            $table->boolean('due_complete')->default(false)->after('due_at');
        });

        Schema::create('task_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('title', 120);
            $table->text('content')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('task_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('title', 120);
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });

        Schema::create('task_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_checklist_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('content', 500);
            $table->boolean('is_complete')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_checklist_items');
        Schema::dropIfExists('task_checklists');
        Schema::dropIfExists('task_sections');

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn([
                'start_at',
                'due_at',
                'due_complete',
            ]);
        });
    }
};
