<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check before DDL: MySQL cannot roll back an ALTER TABLE.
        $admins = [];
        foreach (DB::table('boards')->orderBy('id')->get() as $board) {
            $owner = DB::table('users')->where('id', $board->user_id)
                ->whereNotNull('email_verified_at')->value('id');
            $admin = $owner;
            if (! $admin) {
                throw new RuntimeException("Board {$board->id} needs its creator (user {$board->user_id}) to verify their email before this migration. Verify that account, then rerun migrate. No users are automatically verified or promoted in place of the creator.");
            }
            $admins[$board->id] = $admin;
        }

        Schema::table('board_user', function (Blueprint $table) {
            $table->string('role', 16)->default('member');
        });
        // Older versions allowed duplicate memberships.
        $seen = [];
        foreach (DB::table('board_user')->orderBy('id')->get() as $membership) {
            $key = "{$membership->board_id}:{$membership->user_id}";
            if (isset($seen[$key])) {
                DB::table('board_user')->where('id', $membership->id)->delete();
            }
            $seen[$key] = true;
        }
        foreach ($admins as $boardId => $adminId) {
            DB::table('board_user')->updateOrInsert(
                ['board_id' => $boardId, 'user_id' => $adminId],
                ['role' => 'admin', 'created_at' => now(), 'updated_at' => now()]
            );
        }
        Schema::table('board_user', function (Blueprint $table) {
            $table->unique(['board_id', 'user_id']);
        });
        Schema::create('board_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->nullable(); // null means a reusable share link
            $table->string('role', 16);
            $table->string('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['board_id', 'email']);
        });
        Schema::create('board_membership_revocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Invitation IDs increase monotonically; old bearer links cannot
            // restore this user's access, but a new invitation can.
            $table->unsignedBigInteger('invitation_id_cutoff')->default(0);
            $table->unique(['board_id', 'user_id']);
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('cover_image_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('cover_image_path'));
        Schema::dropIfExists('board_membership_revocations');
        Schema::dropIfExists('board_invitations');
        Schema::table('board_user', function (Blueprint $table) {
            $table->dropUnique(['board_id', 'user_id']);
            $table->dropColumn('role');
        });
    }
};
