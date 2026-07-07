<?php

namespace Scrapkit\LocalizationI18n;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\Contracts\LocaleResolver;

class LocaleManager
{
    public function __construct(
        protected Application $app,
        protected Repository $config,
    ) {}

    /**
     * @return list<string>
     */
    public function supportedLocales(): array
    {
        return $this->config->get('localization-i18n.supported_locales', []);
    }

    public function isSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales(), true);
    }

    public function defaultLocale(): string
    {
        return $this->config->get('localization-i18n.default_locale');
    }

    public function fallbackLocale(): string
    {
        return $this->config->get('localization-i18n.fallback_locale');
    }

    public function currentLocale(): string
    {
        return $this->app->getLocale();
    }

    /**
     * Walk the configured resolvers and return the first supported
     * locale candidate, falling back to the default locale.
     */
    public function determine(Request $request): string
    {
        foreach ($this->config->get('localization-i18n.resolvers', []) as $resolverClass) {
            /** @var LocaleResolver $resolver */
            $resolver = $this->app->make($resolverClass);

            $locale = $resolver->resolve($request);

            if ($locale !== null && $this->isSupported($locale)) {
                return $locale;
            }
        }

        return $this->defaultLocale();
    }

    public function apply(string $locale, ?Request $request = null): void
    {
        $this->app->setLocale($locale);
        $this->app->setFallbackLocale($this->fallbackLocale());

        if ($request?->hasSession()) {
            $request->session()->put(
                $this->config->get('localization-i18n.session_key', 'app_locale'),
                $locale
            );
        }
    }
}
