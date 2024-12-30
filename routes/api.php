<?php

use App\Http\Controllers\Api\Auth\AuthApiController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\PermisssionUserController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/auth', [AuthApiController::class, 'auth'])->name('auth.login');
Route::get('/', fn() => response()->json(['message' => 'ok']));

Route::middleware(['auth:sanctum', 'acl'])->group(function () {
    Route::get('/me', [AuthApiController::class, 'me'])->name('auth.me');
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('auth.logout');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::apiResource('permissions', PermissionController::class);
    Route::post('/users/{user}/permissions-sync',[PermisssionUserController::class, 'syncPermissionOfUser'])->name('users.permission.sync');
    Route::post('/users/{user}/permissions',[PermisssionUserController::class, 'getPermissionOfUser'])->name('users.permission.get');
});

