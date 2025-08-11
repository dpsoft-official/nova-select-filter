<?php declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::middleware(['nova'])->prefix('nova-vendor/nova-multiselect-filter')->group(function () {
    Route::get('search', \Dpsoft\NovaMultiselectFilter\SearchController::class);
});


