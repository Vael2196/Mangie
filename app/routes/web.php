<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BoardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ProjectController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Login/Register
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Board
Route::get('/home', [BoardController::class, 'index'])->middleware('auth')->name('home');
Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
Route::get('/boards/{id}', [BoardController::class, 'show'])->middleware('auth')->name('boards.show');
Route::delete('/boards/{id}', [BoardController::class, 'destroy'])->middleware('auth')->name('boards.destroy');
Route::post('/columns/store', [BoardController::class, 'storeColumn'])->name('columns.store');
Route::post('/tasks/store', [BoardController::class, 'storeTask'])->name('tasks.store');

// Backlog
Route::get('/backlog/{id}', [BoardController::class, 'showBacklog'])->middleware('auth')->name('backlog.show');
Route::post('/backlog/store', [BoardController::class, 'storeTask'])->middleware('auth')->name('backlog.store');

require __DIR__.'/auth.php';
