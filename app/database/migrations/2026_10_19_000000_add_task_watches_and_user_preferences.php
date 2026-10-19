<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('notification_frequency', 16)->default('3_hours');
            $table->boolean('invisible')->default(false);
            $table->timestamp('last_active_at')->nullable();
        });
        Schema::table('tasks', fn (Blueprint $table) => $table->string('color', 16)->nullable());
        Schema::create('task_watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('pending_since')->nullable()->index();
            $table->timestamp('last_notified_at')->nullable();
            $table->unsignedInteger('change_count')->default(0);
            $table->timestamps();
            $table->unique(['task_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_watches');
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('color'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn([
            'notification_frequency', 'invisible', 'last_active_at',
        ]));
    }
};
