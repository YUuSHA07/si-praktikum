<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\FirstLoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

// 1. Rute Publik
Route::get('/', function () {
    return view('welcome');
});

// 2. Grup Rute Auth
Route::middleware(['auth', 'verified'])->group(function () {

    // Rute Force Change Password
    Route::get('/force-change-password', [FirstLoginController::class, 'showChangePasswordForm'])->name('first.login.form');
    Route::post('/force-change-password', [FirstLoginController::class, 'updatePassword'])->name('first.login.update');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- RUTE PROFILE (Pastikan ada 3 rute ini) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Import (Khusus Laboran)
    Route::get('/import-users', [UserImportController::class, 'showImportForm'])->name('user.import.form');
    Route::post('/import-users', [UserImportController::class, 'import'])->name('user.import');

    // user management (Khusus Admin)
    Route::get('/users-management', [UserController::class, 'index'])->name('users.index');
    Route::patch('/users-management/{id}/reset', [UserController::class, 'resetPassword'])->name('users.reset-password');
});

require __DIR__.'/auth.php';