<?php

use AHATechnocrats\OmicsLogic\Http\Controllers\Api\OrganizationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/omics')->middleware(['api', 'throttle:60,1'])->group(function () {
    Route::get('organizations/search', [OrganizationApiController::class, 'search'])
        ->name('omics.api.organizations.search');

    Route::post('organizations', [OrganizationApiController::class, 'store'])
        ->middleware('throttle:20,1')
        ->name('omics.api.organizations.store');
});
