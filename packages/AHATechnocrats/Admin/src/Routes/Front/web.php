<?php

use Illuminate\Support\Facades\Route;
use AHATechnocrats\Admin\Http\Controllers\Controller;

/**
 * Home routes.
 */
Route::get('/', [Controller::class, 'redirectToLogin'])->name('ahatechnocrats.home');
