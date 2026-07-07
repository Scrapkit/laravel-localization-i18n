<?php

namespace Scrapkit\LocalizationI18n\Contracts;

use Illuminate\Http\Request;

interface LocaleResolver
{
    /**
     * Return a locale candidate for the request, or null when this
     * resolver has nothing to offer. Candidates are validated against
     * the supported locales by the LocaleManager.
     */
    public function resolve(Request $request): ?string;
}
