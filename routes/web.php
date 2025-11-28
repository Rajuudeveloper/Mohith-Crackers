<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Agents
    Route::get('/agents', [AgentController::class, 'index'])->name('agents.index');
    Route::get('/agents/create', [AgentController::class, 'create'])->name('agents.create');
    Route::post('/agents/store', [AgentController::class, 'store'])->name('agents.store');
    Route::get('/agents/edit/{id}', [AgentController::class, 'edit'])->name('agents.edit');
    Route::post('/agents/update/{id}', [AgentController::class, 'update'])->name('agents.update');
    Route::get('/agents/delete/{id}', [AgentController::class, 'destroy'])->name('agents.destroy');
    Route::get('/agents/show/{id}', [AgentController::class, 'show'])->name('agents.show');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});

require __DIR__.'/auth.php';
