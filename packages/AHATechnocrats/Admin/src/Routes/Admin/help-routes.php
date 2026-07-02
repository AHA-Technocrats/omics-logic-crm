<?php

use Illuminate\Support\Facades\Route;
use AHATechnocrats\Admin\Http\Controllers\HelpController;

Route::controller(HelpController::class)->prefix('help')->group(function () {
    Route::get('', 'index')->name('admin.help.index');
});
