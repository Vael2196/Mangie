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

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::get('/backlog', [ProjectController::class, 'backlog'])->middleware('auth')->name('backlog.show');
Route::post('/backlog', [ProjectController::class, 'store'])->middleware('auth')->name('backlog.store');
Route::post('/boards', [BoardController::class, 'store'])->name('boards.store');
Route::get('/home', [BoardController::class, 'index'])->middleware('auth')->name('home');
Route::get('/boards/{id}', [BoardController::class, 'show'])->middleware('auth')->name('boards.show');
Route::post('/columns/store', [BoardController::class, 'storeColumn'])->name('columns.store');

require __DIR__.'/auth.php';
