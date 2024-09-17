<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json(['message' => 'ok']) );
Route::get('/users',[UserController::class, 'index'])->name('users.index');


