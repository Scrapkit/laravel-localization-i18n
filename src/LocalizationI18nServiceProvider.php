<?php

namespace Scrapkit\LocalizationI18n;

use Illuminate\Routing\Router;
use Inertia\Inertia;
use Scrapkit\LocalizationI18n\Commands\AddLocaleCommand;
use Scrapkit\LocalizationI18n\Commands\CheckCommand;
use Scrapkit\LocalizationI18n\Commands\GenerateCommand;
use Scrapkit\LocalizationI18n\Commands\UnusedCommand;
use Scrapkit\LocalizationI18n\Http\Middleware\SetLocale;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LocalizationI18nServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-localization-i18n')
            ->hasConfigFile()
            ->hasRoutes(['web', 'api'])
            ->hasCommands([
                GenerateCommand::class,
                CheckCommand::class,
                UnusedCommand::class,
                AddLocaleCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(LocaleManager::class);
    }

    public function packageBooted(): void
    {
        $this->app->make(Router::class)
            ->aliasMiddleware('locale', SetLocale::class);

        $this->shareLocalizationWithInertia();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->package->basePath('/../resources/stubs/frontend') => resource_path('js/i18n'),
            ], 'localization-i18n-frontend');

            $this->publishes([
                $this->package->basePath('/../resources/lang') => $this->app->langPath(),
            ], 'localization-i18n-translations');
        }
    }

    protected function shareLocalizationWithInertia(): void
    {
        if (! config('localization-i18n.inertia.share') || ! class_exists(Inertia::class)) {
            return;
        }

        Inertia::share(
            (string) config('localization-i18n.inertia.key', 'localization'),
            fn (): array => [
                'locale' => $this->app->getLocale(),
                'locales' => config('localization-i18n.supported_locales'),
            ],
        );
    }
}
