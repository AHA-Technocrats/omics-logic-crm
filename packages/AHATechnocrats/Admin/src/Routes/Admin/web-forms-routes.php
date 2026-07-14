<?php

use AHATechnocrats\Admin\Http\Controllers\Settings\WebFormController;
use AHATechnocrats\Admin\Http\Controllers\Settings\WebFormResponseController;
use Illuminate\Support\Facades\Route;

Route::controller(WebFormController::class)->prefix('web-forms')->group(function () {
    Route::get('', 'index')->name('admin.web_forms.index');

    Route::get('create', 'create')->name('admin.web_forms.create');

    Route::post('create', 'store')->name('admin.web_forms.store');

    Route::get('edit/{id?}', 'edit')->name('admin.web_forms.edit');

    Route::put('edit/{id}', 'update')->name('admin.web_forms.update');

    Route::put('{id}/customization', 'updateCustomization')->name('admin.web_forms.customization.update');

    Route::delete('{id}', 'destroy')->name('admin.web_forms.delete');
});
Route::controller(WebFormResponseController::class)->prefix('web-forms')->group(function () {
    Route::get('{id}/responses', 'index')->name('admin.web_forms.responses.index');

    Route::get('{id}/responses/export', 'export')->name('admin.web_forms.responses.export');
});
