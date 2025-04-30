<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/', [UserController::class, 'store']); 
    Route::get('/{user}', [UserController::class, 'show']);
    Route::put('/{user}', [UserController::class, 'update']);
    Route::delete('/{user}', [UserController::class, 'destroy']);
    
    Route::prefix('/{user}/details')->group(function () {
        Route::get('/', [UserController::class, 'indexDetails']);
        Route::post('/', [UserController::class, 'storeDetail']); 
        Route::get('/{detail}', [UserController::class, 'showDetail']);
        Route::put('/{detail}', [UserController::class, 'updateDetail']);
        Route::delete('/{detail}', [UserController::class, 'destroyDetail']);
    });
});