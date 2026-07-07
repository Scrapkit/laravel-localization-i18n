<?php

use Illuminate\Support\Facades\Route;
use Scrapkit\LocalizationI18n\Http\Controllers\LocaleController;

if (! config('localization-i18n.routes.switch_enabled')) {
    return;
}

Route::middleware(config('localization-i18n.routes.middleware', ['web']))
    ->put('/locale', LocaleController::class)
    ->name('locale.update');
