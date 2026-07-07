<?php

use AHATechnocrats\Admin\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

/**
 * Home routes.
 */
Route::get('/', [Controller::class, 'redirectToLogin'])->name('ahatechnocrats.home');
