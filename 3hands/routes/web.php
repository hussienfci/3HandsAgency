<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Users page
Route::get('/users', [UserController::class, 'index'])->name('users.index');

// User API routes
Route::prefix('users')->group(function () {
    Route::get('/api', [UserController::class, 'apiIndex'])->name('users.api.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::put('/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/bulk-delete', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
});






