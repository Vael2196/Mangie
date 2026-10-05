<?php

namespace App\Console\Commands;

use App\Models\Board;
use App\Models\BoardLabel;
use App\Models\Column;
use App\Models\Project;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class BootstrapMangie extends Command
{
    protected $signature = 'mangie:bootstrap
                            {--name= : Initial administrator name}
                            {--email= : Initial administrator email}';

    protected $description =
        'Create the first Mangie administrator, project, and backlog';

    public function handle(): int
    {
        if (
            User::query()->exists()
            || Project::query()->exists()
            || Board::query()->exists()
        ) {
            $this->error(
                'Mangie already contains data. This command is only for a fresh database.'
            );

            return self::FAILURE;
        }

        $name = (string) (
            $this->option('name')
            ?: $this->ask('Administrator name')
        );

        $email = (string) (
            $this->option('email')
            ?: $this->ask('Administrator email')
        );

        $password = (string) $this->secret(
            'Administrator password (at least 12 characters)'
        );

        $passwordConfirmation = (string) $this->secret(
            'Confirm administrator password'
        );

        $validator = Validator::make(
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' =>
                    $passwordConfirmation,
            ],
            [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'email' => [
                    'required',
                    'email',
                    'max:255',
                    'unique:users,email',
                ],
                'password' => [
                    'required',
                    'confirmed',
                    Password::min(12),
                ],
            ]
        );

        if ($validator->fails()) {
            foreach (
                $validator->errors()->all()
                as $message
            ) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use (
            $name,
            $email,
            $password
        ) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);

            $user->forceFill([
                'admin' => true,
                'email_verified_at' => now(),
            ])->save();

            $project = Project::create([
                'name' => 'Mangie',
            ]);

            $backlog = new Board([
                'name' => 'Product Backlog',
                'project_id' => $project->id,
                'user_id' => $user->id,
            ]);

            $backlog->id = (int) config(
                'mangie.product_backlog_board_id',
                1
            );

            $backlog->save();
            $backlog->users()->attach($user->id);

            Column::create([
                'board_id' => $backlog->id,
                'name' => 'Backlog',
                'position' => 1,
            ]);

            foreach (
                config('mangie.standard_labels', [])
                as $index => $label
            ) {
                BoardLabel::create([
                    'board_id' => $backlog->id,
                    'name' => $label['name'],
                    'color' => $label['color'],
                    'position' => $index + 1,
                ]);
            }
        }, 3);

        $this->info(
            'Mangie is ready. Sign in with the administrator account you just created.'
        );

        return self::SUCCESS;
    }
}
