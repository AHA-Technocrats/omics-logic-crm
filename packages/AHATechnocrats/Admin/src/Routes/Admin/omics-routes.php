<?php

use AHATechnocrats\Admin\Http\Controllers\OmicsLogic\AuditLogController;
use AHATechnocrats\Admin\Http\Controllers\OmicsLogic\ConnectorController;
use AHATechnocrats\Admin\Http\Controllers\OmicsLogic\MergeReviewController;
use AHATechnocrats\Admin\Http\Controllers\OmicsLogic\OrganizationMergeReviewController;
use AHATechnocrats\Admin\Http\Controllers\OmicsLogic\ReportController;
use AHATechnocrats\Admin\Http\Controllers\OmicsLogic\SegmentController;
use Illuminate\Support\Facades\Route;

Route::prefix('omics')->group(function () {
    Route::controller(ConnectorController::class)->prefix('connectors')->group(function () {
        Route::get('', 'index')->name('admin.omics.connectors.index');
        Route::get('edit/{id}', 'edit')->name('admin.omics.connectors.edit');
        Route::put('edit/{id}', 'update')->name('admin.omics.connectors.update');
        Route::post('{id}/sync', 'sync')->name('admin.omics.connectors.sync');
        Route::post('{id}/reset-sync', 'resetSync')->name('admin.omics.connectors.reset-sync');
    });

    Route::controller(SegmentController::class)->prefix('segments')->group(function () {
        Route::get('', 'index')->name('admin.omics.segments.index');
        Route::get('create', 'create')->name('admin.omics.segments.create');
        Route::post('create', 'store')->name('admin.omics.segments.store');
        Route::get('edit/{id}', 'edit')->name('admin.omics.segments.edit');
        Route::put('edit/{id}', 'update')->name('admin.omics.segments.update');
        Route::delete('{id}', 'destroy')->name('admin.omics.segments.delete');
        Route::post('mass-destroy', 'massDestroy')->name('admin.omics.segments.mass_delete');
    });

    Route::get('merge-review', [MergeReviewController::class, 'index'])->name('admin.omics.merge.index');
    Route::post('merge-review/{id}/resolve', [MergeReviewController::class, 'resolve'])->name('admin.omics.merge.resolve');

    Route::post('merge-review/organizations/{id}/resolve', [OrganizationMergeReviewController::class, 'resolve'])->name('admin.omics.merge_organizations.resolve');

    Route::get('reports', [ReportController::class, 'index'])->name('admin.omics.reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('admin.omics.reports.export');

    Route::get('audit-log', [AuditLogController::class, 'index'])->name('admin.omics.audit.index');
    Route::get('audit-log/{id}', [AuditLogController::class, 'show'])->name('admin.omics.audit.view');
    Route::post('audit-log/{id}/undo', [AuditLogController::class, 'undo'])->name('admin.omics.audit.undo');
});
