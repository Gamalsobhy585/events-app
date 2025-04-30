<?php

use Illuminate\Support\Facades\Route;

Route::middleware(["cors"])->group(function () {
    require __DIR__ . '/api/user.php';
});
