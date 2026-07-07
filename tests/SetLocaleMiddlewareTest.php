<?php

use Illuminate\Support\Facades\Route;
use Scrapkit\LocalizationI18n\Tests\TestSupport\FixedLocaleResolver;

beforeEach(function () {
    Route::middleware(['web', 'locale'])->get('/current-locale', fn () => app()->getLocale());
});

it('uses the default locale when nothing else matches', function () {
    // The test client always sends an Accept-Language header, so an
    // unsupported one is needed to exercise the default-locale fallback.
    $this->get('/current-locale', ['Accept-Language' => 'de-DE,de;q=0.9'])
        ->assertOk()
        ->assertContent('it');
});

it('resolves the locale from the query parameter', function () {
    $this->get('/current-locale?locale=en')
        ->assertOk()
        ->assertContent('en');
});

it('ignores an unsupported query parameter locale', function () {
    $this->get('/current-locale?locale=de', ['Accept-Language' => 'de-DE,de;q=0.9'])
        ->assertOk()
        ->assertContent('it');
});

it('resolves the locale from the session', function () {
    $this->withSession(['app_locale' => 'en'])
        ->get('/current-locale')
        ->assertOk()
        ->assertContent('en');
});

it('prefers the query parameter over the session', function () {
    $this->withSession(['app_locale' => 'it'])
        ->get('/current-locale?locale=en')
        ->assertOk()
        ->assertContent('en');
});

it('falls through to the session when the query parameter is unsupported', function () {
    $this->withSession(['app_locale' => 'en'])
        ->get('/current-locale?locale=de')
        ->assertOk()
        ->assertContent('en');
});

it('resolves the locale from the Accept-Language header', function () {
    $this->get('/current-locale', ['Accept-Language' => 'en-GB,en;q=0.9'])
        ->assertOk()
        ->assertContent('en');
});

it('maps a regional Accept-Language variant to a supported locale', function () {
    $this->get('/current-locale', ['Accept-Language' => 'en-US'])
        ->assertOk()
        ->assertContent('en');
});

it('skips unsupported Accept-Language entries by quality order', function () {
    $this->get('/current-locale', ['Accept-Language' => 'de-DE,de;q=0.9,en;q=0.5'])
        ->assertOk()
        ->assertContent('en');
});

it('prefers the session over the Accept-Language header', function () {
    $this->withSession(['app_locale' => 'it'])
        ->get('/current-locale', ['Accept-Language' => 'en'])
        ->assertOk()
        ->assertContent('it');
});

it('persists the determined locale in the session', function () {
    $this->get('/current-locale?locale=en')
        ->assertSessionHas('app_locale', 'en');
});

it('handles requests without a session', function () {
    Route::middleware('locale')->get('/stateless-locale', fn () => app()->getLocale());

    $this->get('/stateless-locale?locale=en')
        ->assertOk()
        ->assertContent('en');
});

it('applies the configured fallback locale to the application', function () {
    Route::middleware(['web', 'locale'])->get('/fallback-locale', fn () => app()->getFallbackLocale());

    config()->set('localization-i18n.fallback_locale', 'it');

    $this->get('/fallback-locale')
        ->assertOk()
        ->assertContent('it');
});

it('supports custom resolvers from the configuration', function () {
    config()->set('localization-i18n.resolvers', [FixedLocaleResolver::class]);

    $this->get('/current-locale')
        ->assertOk()
        ->assertContent('en');
});
