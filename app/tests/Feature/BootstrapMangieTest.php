<?php

use App\Models\Board;
use App\Models\BoardLabel;
use App\Models\Column;
use App\Models\Project;
use App\Models\User;

it('bootstraps a fresh production database safely', function () {
    $this->artisan('mangie:bootstrap')
        ->expectsQuestion(
            'Administrator name',
            'Mangie Admin'
        )
        ->expectsQuestion(
            'Administrator email',
            'admin@example.com'
        )
        ->expectsQuestion(
            'Administrator password (at least 12 characters)',
            'a-strong-password'
        )
        ->expectsQuestion(
            'Confirm administrator password',
            'a-strong-password'
        )
        ->expectsOutput(
            'Mangie is ready. Sign in with the administrator account you just created.'
        )
        ->assertSuccessful();

    $user = User::query()->sole();
    $backlog = Board::query()->sole();

    expect($user->admin)->toBeTruthy()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Project::query()->count())->toBe(1)
        ->and($backlog->id)->toBe(
            (int) config(
                'mangie.product_backlog_board_id',
                1
            )
        )
        ->and($backlog->user_id)->toBe($user->id)
        ->and(
            $backlog
                ->users()
                ->whereKey($user->id)
                ->exists()
        )->toBeTrue()
        ->and(Column::query()->value('name'))->toBe('Backlog')
        ->and(BoardLabel::query()->count())->toBe(
            count(config('mangie.standard_labels'))
        );
});

it('refuses to bootstrap a database that already contains data', function () {
    User::factory()->create();

    $this->artisan('mangie:bootstrap')
        ->expectsOutput(
            'Mangie already contains data. This command is only for a fresh database.'
        )
        ->assertFailed();
});
