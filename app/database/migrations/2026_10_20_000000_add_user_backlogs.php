<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->boolean('is_backlog')->default(false)->index();
        });
        Schema::table('users', function (Blueprint $table) {
            // A preference, not an ownership link: every use rechecks membership.
            // Avoid a circular cascade between users and boards.
            $table->unsignedBigInteger('backlog_board_id')->nullable()->index();
        });

        $legacyId = (int) config('mangie.product_backlog_board_id', 1);
        DB::table('boards')->where('id', $legacyId)->update(['is_backlog' => true]);
        // Retain the existing workflow for explicitly authorised editors only.
        // No memberships are created and no existing tasks are moved.
        DB::table('users')->whereNotNull('email_verified_at')->whereIn('id',
            DB::table('board_user')->select('user_id')->where('board_id', $legacyId)
                ->whereIn('role', ['admin', 'member'])
        )->update(['backlog_board_id' => $legacyId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['backlog_board_id']);
            $table->dropColumn('backlog_board_id');
        });
        Schema::table('boards', function (Blueprint $table) {
            $table->dropIndex(['is_backlog']);
            $table->dropColumn('is_backlog');
        });
    }
};
