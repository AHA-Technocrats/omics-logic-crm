<?php

use AHATechnocrats\WebForm\Http\Controllers\WebFormController;
use Illuminate\Support\Facades\Route;

Route::controller(WebFormController::class)->middleware(['web', 'admin_locale'])->prefix('web-forms')->group(function () {
    Route::get('forms/{id}/form.js', 'formJS')->name('admin.settings.web_forms.form_js');

    Route::get('forms/{id}/form.html', 'preview')->name('admin.settings.web_forms.preview');

    Route::get('forms/{id}/thank-you', 'thankYou')->name('admin.settings.web_forms.thank_you');

    Route::get('organizations/search', 'searchOrganizations')
        ->name('admin.settings.web_forms.organizations.search');

    Route::post('forms/{id}/check-email', 'checkEmail')
        ->middleware('throttle:60,1')
        ->name('admin.settings.web_forms.check_email');

    Route::post('forms/{id}', 'formStore')
        ->middleware('throttle:30,1')
        ->name('admin.settings.web_forms.form_store');

    Route::group(['middleware' => ['user']], function () {
        Route::get('form/{id}/form.html', 'view')->name('admin.settings.web_forms.view');
    });
});
