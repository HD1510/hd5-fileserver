<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShareController;
use Illuminate\Support\Facades\Route;

// ── Public ────────────────────────────────────────────────────────────────────
Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/s/{token}', [ShareController::class, 'show'])->name('shares.show');
Route::post('/s/{token}', [ShareController::class, 'authenticate'])->name('shares.authenticate');
Route::get('/s/{token}/download/{fileId?}', [ShareController::class, 'download'])->name('shares.download');

// ── Authenticated ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // File browser
    Route::get('/files', [FolderController::class, 'index'])->name('files.index');
    Route::get('/files/folder/{folder}', [FolderController::class, 'show'])->name('folders.show');

    // Folder management
    Route::post('/folders', [FolderController::class, 'store'])->name('folders.store');
    Route::patch('/folders/{folder}', [FolderController::class, 'update'])->name('folders.update');
    Route::delete('/folders/{folder}', [FolderController::class, 'destroy'])->name('folders.destroy');

    // File management
    Route::post('/files/upload', [FileController::class, 'upload'])->name('files.upload');
    Route::get('/files/{file}/download', [FileController::class, 'download'])->name('files.download');
    Route::patch('/files/{file}', [FileController::class, 'update'])->name('files.update');
    Route::patch('/files/{file}/move', [FileController::class, 'move'])->name('files.move');
    Route::delete('/files/{file}', [FileController::class, 'destroy'])->name('files.destroy');

    // Share management (auth)
    Route::get('/shares', [ShareController::class, 'index'])->name('shares.index');
    Route::post('/shares', [ShareController::class, 'store'])->name('shares.store');
    Route::patch('/shares/{share}', [ShareController::class, 'update'])->name('shares.update');
    Route::delete('/shares/{share}', [ShareController::class, 'destroy'])->name('shares.destroy');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', AdminUserController::class);
});

require __DIR__ . '/auth.php';
