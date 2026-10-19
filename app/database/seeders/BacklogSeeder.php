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
                'is_backlog' => true,
                'user_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('board_user')->insert([
                'board_id' => $id, 'user_id' => 1, 'role' => 'admin',
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('users')->where('id', 1)->whereNull('backlog_board_id')
                ->update(['backlog_board_id' => $id]);

            DB::table('columns')->insert([
                "board_id" => $id,
                "name" => "Backlog",
                'position' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach (
                config('mangie.standard_labels', [])
                as $index => $label
            ) {
                DB::table('board_labels')->insert([
                    'board_id' => $id,
                    'name' => $label['name'],
                    'color' => $label['color'],
                    'position' => $index + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
