<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sprint', [ProjectController::class, 'sprint'])
    ->middleware(['auth', 'verified'])
    ->name('sprint');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route::get('/home', [ProjectController::class, 'index'])->name('home');
Route::get('/backlog', [ProjectController::class, 'backlog'])->name('backlog');

require __DIR__.'/auth.php';
