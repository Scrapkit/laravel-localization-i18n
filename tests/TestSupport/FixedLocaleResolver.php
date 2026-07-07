<?php

namespace Scrapkit\LocalizationI18n\Tests\TestSupport;

use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\Contracts\LocaleResolver;

class FixedLocaleResolver implements LocaleResolver
{
    public function resolve(Request $request): ?string
    {
        return 'en';
    }
}
