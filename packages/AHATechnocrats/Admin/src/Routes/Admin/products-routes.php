<?php

use Illuminate\Support\Facades\Route;

/**
 * Legacy product URLs redirect to campaigns.
 */
Route::redirect('products', 'campaigns', 301);
Route::redirect('products/create', 'campaigns/create', 301);
Route::get('products/view/{id}', fn ($id) => redirect()->route('admin.campaigns.view', $id, absolute: false)->setStatusCode(301));
Route::get('products/edit/{id}', fn ($id) => redirect()->route('admin.campaigns.edit', $id, absolute: false)->setStatusCode(301));
