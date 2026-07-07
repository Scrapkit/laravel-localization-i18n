<?php

namespace Scrapkit\LocalizationI18n\Resolvers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\Contracts\LocaleResolver;

class SessionResolver implements LocaleResolver
{
    public function __construct(protected Repository $config) {}

    public function resolve(Request $request): ?string
    {
        if (! $request->hasSession()) {
            return null;
        }

        $value = $request->session()->get(
            $this->config->get('localization-i18n.session_key', 'app_locale')
        );

        return is_string($value) ? $value : null;
    }
}
