<?php

use AHATechnocrats\Admin\Http\Controllers\Products\ActivityController;
use AHATechnocrats\Admin\Http\Controllers\Products\ProductController;
use AHATechnocrats\Admin\Http\Controllers\Products\TagController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['user']], function () {
    Route::controller(ProductController::class)->prefix('campaigns')->group(function () {
        Route::get('', 'index')->name('admin.campaigns.index');

        Route::get('create', 'create')->name('admin.campaigns.create');

        Route::post('create', 'store')->name('admin.campaigns.store');

        Route::get('view/{id}', 'view')->name('admin.campaigns.view');

        Route::get('edit/{id}', 'edit')->name('admin.campaigns.edit');

        Route::put('edit/{id}', 'update')->name('admin.campaigns.update');

        Route::get('search', 'search')->name('admin.campaigns.search');

        Route::get('{id}/warehouses', 'warehouses')->name('admin.campaigns.warehouses');

        Route::post('{id}/inventories/{warehouseId?}', 'storeInventories')->name('admin.campaigns.inventories.store');

        Route::delete('{id}', 'destroy')->name('admin.campaigns.delete');

        Route::post('mass-destroy', 'massDestroy')->name('admin.campaigns.mass_delete');

        Route::controller(ActivityController::class)->prefix('{id}/activities')->group(function () {
            Route::get('', 'index')->name('admin.campaigns.activities.index');
        });

        Route::controller(TagController::class)->prefix('{id}/tags')->group(function () {
            Route::post('', 'attach')->name('admin.campaigns.tags.attach');

            Route::delete('', 'detach')->name('admin.campaigns.tags.detach');
        });
    });
});
