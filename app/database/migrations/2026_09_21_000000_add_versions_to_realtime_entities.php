<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->unsignedBigInteger('version')->default(1);
        });

        Schema::table('columns', function (Blueprint $table) {
            $table->unsignedBigInteger('version')->default(1);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('version')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('version');
        });

        Schema::table('columns', function (Blueprint $table) {
            $table->dropColumn('version');
        });

        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};
