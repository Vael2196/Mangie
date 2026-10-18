<?php

use App\Models\Board;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('backfills creators as admins and deduplicates legacy memberships', function () {
    $owner = User::factory()->create();
    $member = User::factory()->create();
    $board = Board::create([
        'name' => 'Existing sprint', 'project_id' => Project::create(['name' => 'Existing project'])->id,
        'user_id' => $owner->id,
    ]);
    $migration = require database_path('migrations/2026_10_18_000000_add_board_roles_and_invitations.php');
    $migration->down();
    foreach ([$owner->id, $member->id, $member->id] as $id) {
        DB::table('board_user')->insert(['board_id' => $board->id, 'user_id' => $id, 'created_at' => now(), 'updated_at' => now()]);
    }
    $migration->up();
    expect($board->roleFor($owner))->toBe('admin')->and($board->roleFor($member))->toBe('member')
        ->and($board->users()->count())->toBe(2);
});

it('stops before schema changes if an existing creator is not verified', function () {
    $owner = User::factory()->create();
    $board = Board::create([
        'name' => 'Existing sprint', 'project_id' => Project::create(['name' => 'Existing project'])->id,
        'user_id' => $owner->id,
    ]);
    $migration = require database_path('migrations/2026_10_18_000000_add_board_roles_and_invitations.php');
    $migration->down();
    $owner->forceFill(['email_verified_at' => null])->save();
    expect(fn () => $migration->up())->toThrow(RuntimeException::class, "Board {$board->id} needs its creator");
    expect(Schema::hasColumn('board_user', 'role'))->toBeFalse()
        ->and($owner->fresh()->hasVerifiedEmail())->toBeFalse();
    $owner->forceFill(['email_verified_at' => now()])->save();
    $migration->up();
});

it('preserves shared boards when a creator deletes their account after appointing another admin', function () {
    $owner = User::factory()->create();
    $successor = User::factory()->create();
    $board = Board::create([
        'name' => 'Keep our board', 'project_id' => Project::create(['name' => 'Shared project'])->id,
        'user_id' => $owner->id,
    ]);
    $board->users()->attach($successor->id, ['role' => 'admin']);
    $this->actingAs($owner)->delete('/profile', ['password' => 'password'])->assertRedirect('/');
    expect($owner->fresh())->toBeNull()->and($board->fresh()->user_id)->toBe($successor->id)
        ->and($board->roleFor($successor))->toBe('admin');
});

it('checks current admins after the upgrade rather than a former creators verification', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create();
    $board = Board::create([
        'name' => 'Shared board', 'project_id' => Project::create(['name' => 'Admin check'])->id,
        'user_id' => $owner->id,
    ]);
    $board->users()->attach($admin->id, ['role' => 'admin']);
    $board->users()->detach($owner->id);
    $owner->forceFill(['email_verified_at' => null])->save();
    $this->artisan('mangie:check-board-admins')->assertSuccessful();
});
