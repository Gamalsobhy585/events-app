<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::get('/users/{user}/details', [UserController::class, 'details']);
