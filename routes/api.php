<?php

use Illuminate\Support\Facades\Route;
use Scrapkit\LocalizationI18n\Http\Controllers\TranslationController;

if (! config('localization-i18n.api.enabled')) {
    return;
}

Route::middleware(config('localization-i18n.api.middleware', ['api']))
    ->prefix(config('localization-i18n.api.prefix', 'api/translations'))
    ->get('{locale}/{namespace?}', TranslationController::class)
    ->name('translations.show');
