<?php

use App\Http\Controllers\Api\Firebase\FormController;
use App\Http\Controllers\Api\Firebase\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('firebase')
    ->middleware(['api', 'firebase.api', 'throttle:60,1'])
    ->group(function () {
        Route::get('users', [UserController::class, 'show']);
        Route::get('forms', [FormController::class, 'index']);
    });
