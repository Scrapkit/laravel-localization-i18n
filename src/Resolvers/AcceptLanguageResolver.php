<?php

namespace Scrapkit\LocalizationI18n\Resolvers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\Contracts\LocaleResolver;

class AcceptLanguageResolver implements LocaleResolver
{
    public function __construct(protected Repository $config) {}

    public function resolve(Request $request): ?string
    {
        $supported = $this->config->get('localization-i18n.supported_locales', []);

        foreach ($request->getLanguages() as $language) {
            $candidate = str_replace('_', '-', $language);

            if (in_array($candidate, $supported, true)) {
                return $candidate;
            }

            $base = explode('-', $candidate)[0];

            if (in_array($base, $supported, true)) {
                return $base;
            }
        }

        return null;
    }
}
