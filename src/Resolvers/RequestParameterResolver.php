<?php

namespace Scrapkit\LocalizationI18n\Resolvers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\Contracts\LocaleResolver;

class RequestParameterResolver implements LocaleResolver
{
    public function __construct(protected Repository $config) {}

    public function resolve(Request $request): ?string
    {
        $parameter = $this->config->get('localization-i18n.request_parameter', 'locale');

        $value = $request->route($parameter) ?? $request->query($parameter);

        return is_string($value) ? $value : null;
    }
}
