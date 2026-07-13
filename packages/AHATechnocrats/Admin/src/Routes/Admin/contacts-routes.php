<?php

use AHATechnocrats\Admin\Http\Controllers\Contact\OrganizationController;
use AHATechnocrats\Admin\Http\Controllers\Contact\Persons\ActivityController;
use AHATechnocrats\Admin\Http\Controllers\Contact\Persons\PersonCampaignController;
use AHATechnocrats\Admin\Http\Controllers\Contact\Persons\PersonController;
use AHATechnocrats\Admin\Http\Controllers\Contact\Persons\PersonLeadController;
use AHATechnocrats\Admin\Http\Controllers\Contact\Persons\PersonPortalController;
use AHATechnocrats\Admin\Http\Controllers\Contact\Persons\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('contacts')->group(function () {
    /**
     * Mass Assign routes.
     */
    Route::controller(\AHATechnocrats\Admin\Http\Controllers\MassAssignController::class)->prefix('mass-assign')->group(function () {
        Route::get('', 'index')->name('admin.mass_assign.index');
        Route::get('entities', 'getEntities')->name('admin.mass_assign.entities');
        Route::get('users', 'getUsers')->name('admin.mass_assign.users');
        Route::post('assign', 'assign')->name('admin.mass_assign.assign');
    });

    /**
     * Persons routes.
     */
    Route::controller(PersonController::class)->prefix('persons')->group(function () {
        Route::get('', 'index')->name('admin.contacts.persons.index');

        Route::get('create', 'create')->name('admin.contacts.persons.create');

        Route::post('create', 'store')->name('admin.contacts.persons.store');

        Route::get('view/{id}', 'show')->name('admin.contacts.persons.view');

        Route::get('edit/{id}', 'edit')->name('admin.contacts.persons.edit');

        Route::put('edit/{id}', 'update')->name('admin.contacts.persons.update');

        Route::get('search', 'search')->name('admin.contacts.persons.search');

        Route::get('delete-preview/{id}', 'deletePreview')->name('admin.contacts.persons.delete-preview');

        Route::middleware(['throttle:100,60'])->delete('{id}', 'destroy')->name('admin.contacts.persons.delete');

        Route::post('mass-destroy', 'massDestroy')->name('admin.contacts.persons.mass_delete');

        /**
         * Tag routes.
         */
        Route::controller(TagController::class)->prefix('{id}/tags')->group(function () {
            Route::post('', 'attach')->name('admin.contacts.persons.tags.attach');

            Route::delete('', 'detach')->name('admin.contacts.persons.tags.detach');
        });

        /**
         * Activity routes.
         */
        Route::controller(ActivityController::class)->prefix('{id}/activities')->group(function () {
            Route::get('', 'index')->name('admin.contacts.persons.activities.index');
        });

        Route::controller(ActivityController::class)->prefix('{id}/activities-api')->group(function () {
            Route::get('', 'apiIndex')->name('admin.contacts.persons.activities.api_index');
        });

        Route::controller(PersonPortalController::class)->prefix('{id}/portal')->group(function () {
            Route::get('', 'show')->name('admin.contacts.persons.portal');
        });

        Route::controller(PersonLeadController::class)->prefix('{id}/leads')->group(function () {
            Route::get('', 'index')->name('admin.contacts.persons.leads.index');
        });

        Route::controller(PersonCampaignController::class)->prefix('{id}/campaigns')->group(function () {
            Route::get('', 'index')->name('admin.contacts.persons.campaigns.index');
            Route::get('{leadId}', 'show')->name('admin.contacts.persons.campaigns.show');
        });
    });

    /**
     * Organization routes.
     */
    Route::controller(OrganizationController::class)->prefix('organizations')->group(function () {
        Route::get('', 'index')->name('admin.contacts.organizations.index');

        Route::get('create', 'create')->name('admin.contacts.organizations.create');

        Route::post('create', 'store')->name('admin.contacts.organizations.store');

        Route::get('view/{id}', 'show')->name('admin.contacts.organizations.view');

        Route::get('edit/{id?}', 'edit')->name('admin.contacts.organizations.edit');

        Route::put('edit/{id}', 'update')->name('admin.contacts.organizations.update');
        
        Route::get('search', 'search')->name('admin.contacts.organizations.search');

        Route::get('delete-preview/{id}', 'deletePreview')->name('admin.contacts.organizations.delete-preview');

        Route::delete('{id}', 'destroy')->name('admin.contacts.organizations.delete');

        Route::put('mass-destroy', 'massDestroy')->name('admin.contacts.organizations.mass_delete');
    });
});
