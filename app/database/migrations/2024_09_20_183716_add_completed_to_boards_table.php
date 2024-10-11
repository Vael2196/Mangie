<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedToBoardsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->boolean('completed')->default(0)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('boards', function (Blueprint $table) {
            $table->dropColumn('completed');
        });
    }
}
