<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table
                ->string('background_color', 7)
                ->default('#eef2ff')
                ->after('date_ended');

            $table
                ->string('background_image_path')
                ->nullable()
                ->after('background_color');
        });
    }

    public function down(): void
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn([
                'background_color',
                'background_image_path',
            ]);
        });
    }
};
