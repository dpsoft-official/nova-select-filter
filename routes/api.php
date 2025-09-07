<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['nova', 'api'])->prefix('nova-vendor/nova-multiselect-filter')->group(function () {
    Route::get('search', \Dpsoft\NovaMultiselectFilter\SearchController::class);
});
