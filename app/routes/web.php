<?php

use App\Http\Controllers\BacklogController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BoardMemberController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskMoveController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');

    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

    Route::get(
        '/adduser',
        [ProfileController::class, 'showAddUsers']
    )->name('profile.add-user');

    Route::post(
        '/adduser',
        [ProfileController::class, 'addUser']
    )->name('profile.users.add');

    Route::patch(
        '/adduser',
        [ProfileController::class, 'updateUser']
    )->name('profile.user_update');

    Route::delete(
        '/removeuser/{id}',
        [ProfileController::class, 'removeUser']
    )->name('profile.user_remove');

    Route::get(
        '/dashboard',
        [BoardController::class, 'dashboard']
    )->name('dashboard');

    Route::get(
        '/home',
        [BoardController::class, 'home']
    )->name('home');

    Route::post(
        '/boards',
        [BoardController::class, 'store']
    )->name('boards.store');

    Route::post(
        '/boards/updateStatus',
        [BoardController::class, 'updateStatus']
    )->name('boards.updateStatus');

    Route::post(
        '/boards/startSprint',
        [BoardController::class, 'startSprint']
    )->name('boards.startSprint');

    Route::get(
        '/boards/{board}/burndownChart',
        [BoardController::class, 'showBurndownChart']
    )->name('boards.burndownChart');

    Route::post(
        '/boards/{board}/endSprint',
        [BoardController::class, 'completeBoard']
    )->name('boards.complete');

    Route::get(
        '/boards/{board}',
        [BoardController::class, 'show']
    )->name('boards.show');

    Route::delete(
        '/boards/{board}',
        [BoardController::class, 'destroy']
    )->name('boards.destroy');

    Route::post(
        '/columns/store',
        [ColumnController::class, 'store']
    )->name('columns.store');

    Route::patch(
        '/columns/{column}/name',
        [ColumnController::class, 'updateName']
    )->name('columns.name');

    Route::patch(
        '/columns/{column}/color',
        [ColumnController::class, 'updateColor']
    )->name('columns.color');

    Route::post(
        '/columns/{column}/copy',
        [ColumnController::class, 'copy']
    )->name('columns.copy');

    Route::delete(
        '/columns/{column}',
        [ColumnController::class, 'destroy']
    )->name('columns.destroy');

    Route::post(
        '/tasks/store',
        [TaskController::class, 'store']
    )->name('tasks.store');

    Route::patch(
        '/tasks/bulk-move',
        [TaskMoveController::class, 'bulk']
    )->name('tasks.bulk-move');

    Route::patch(
        '/tasks/{task}/move',
        [TaskMoveController::class, 'update']
    )->name('tasks.move');

    Route::patch(
        '/tasks/{task}',
        [TaskController::class, 'update']
    )->name('tasks.update');

    Route::get(
        '/backlog/{view}',
        [BacklogController::class, 'show']
    )->name('backlog.show');

    Route::post(
        '/search-users',
        [BoardMemberController::class, 'search']
    )->name('users.search');

    Route::post(
        '/boards/{board}/add-user',
        [BoardMemberController::class, 'store']
    )->name('boards.addUser');

    Route::delete(
        '/projects/{project}/users/{user}',
        [ProjectController::class, 'removeUser']
    )->name('projects.users.remove');
});

require __DIR__.'/auth.php';
