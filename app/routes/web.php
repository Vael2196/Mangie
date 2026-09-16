<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', [ProjectController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

// Login/Register
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Logout
// Route::post('/login', [ProfileController::class, 'logout'])->middleware('auth')->name('logout');

// Manage Users
Route::get('/adduser', [ProfileController::class, 'showAddUsers'])->middleware('auth')->name('profile.add-user');
Route::post('/adduser', [ProfileController::class, 'addUser'])->middleware('auth')->name('profile.users.add');
Route::patch('/adduser', [ProfileController::class, 'updateUser'])->middleware('auth')->name('profile.user_update');
Route::delete('/removeuser/{id}', [ProfileController::class, 'removeUser'])->middleware('auth')->name('profile.user_remove');

// Board
Route::get('/dashboard', [BoardController::class, 'dashboard']) ->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/home', [BoardController::class, 'home']) ->middleware('auth')->name('home');
Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
Route::get('/boards/{id}', [BoardController::class, 'show'])->middleware('auth')->name('boards.show');
Route::post('/boards/move', [BoardController::class, 'moveColumnTasks'])->middleware('auth')->name('boards.moveTasks');
Route::delete('/boards/{id}', [BoardController::class, 'destroy'])->middleware('auth')->name('boards.destroy');

Route::post('/columns/store', [BoardController::class, 'storeColumn'])->middleware('auth')->name('columns.store');
Route::patch('/columns/{column}/name', [BoardController::class, 'updateColumnName'])->middleware('auth')->name('columns.name');
Route::patch('/columns/{column}/color', [BoardController::class, 'updateColumnColor'])->middleware('auth')->name('columns.color');
Route::post('/columns/{column}/copy', [BoardController::class, 'copyColumn'])->middleware('auth')->name('columns.copy');
Route::delete('/columns/{column}', [BoardController::class, 'destroyColumn'])->middleware('auth')-> name('columns.destroy');
Route::post('/tasks/store', [BoardController::class, 'storeTask'])->middleware('auth')->name('tasks.store');
Route::post('/tasks/update', [BoardController::class, 'updateTask'])->middleware('auth')->name('tasks.update');
Route::post('/boards/updateStatus', [BoardController::class, 'updateStatus'])->name('boards.updateStatus');
Route::post('/boards/startSprint', [BoardController::class, 'startSprint'])->name('boards.startSprint');
Route::post('/boards/{id}/endSprint', [BoardController::class, 'completeBoard'])->name('boards.complete');

Route::post('/boards/{id}/burndownChart', [BoardController::class, 'showBurndownChart'])->name('boards.burndownChart');

Route::patch('/tasks/{task}/move', [BoardController::class, 'moveTask'])->middleware('auth')->name('tasks.move');

// Backlog
Route::get('/backlog/{view}', [BoardController::class, 'showBacklog'])->middleware('auth')->name('backlog.show');
Route::post('/backlog/store', [BoardController::class, 'storeTask'])->middleware('auth')->name('backlog.store');
Route::post('/backlog/move', [BoardController::class, 'moveTasks'])->middleware('auth')->name('backlog.moveTasks');

//User Deletion
Route::delete('/projects/{project}/users/{user}', [ProjectController::class, 'removeUser'])->middleware('auth')->name('projects.users.remove');
require __DIR__.'/auth.php';

//Search and add user to a board
Route::post('/search-users', [BoardController::class, 'searchUsers']) ->middleware('auth') ->name('users.search');
Route::post('/boards/{board}/add-user', [BoardController::class, 'addUserToBoard']) ->middleware('auth')->name('boards.addUser');