<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BacklogSeeder extends Seeder
{
    /**
     * Run the backlog seeds for every project
     */
    public function run(): void
    {
        DB::table('projects')->get()->each(function ($project) {
            $id = DB::table('boards')->insertGetId([
                'project_id' => $project->id,
                'name' => 'Project Backlog',
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('columns')->insert([
                "board_id" => $id,
                "name" => "Backlog",
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        });
    }
}
