<?php

it('reports translation keys that are never referenced', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['used' => 'x', 'orphan' => 'y'],
        'en/app.php' => ['used' => 'x', 'orphan' => 'y'],
    ]));
    config()->set('localization-i18n.scan_paths', [
        makeSourceDir(['Service.php' => "<?php __('app.used');"]),
    ]);

    $this->artisan('translations:unused')
        ->expectsOutputToContain('app.orphan')
        ->doesntExpectOutputToContain('app.used')
        ->assertSuccessful();
});

it('detects usage from react t() calls with a namespace prefix', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['title' => 'x'],
    ]));
    config()->set('localization-i18n.scan_paths', [
        makeSourceDir(['Page.tsx' => "const x = t('app:title');"]),
    ]);

    $this->artisan('translations:unused')
        ->doesntExpectOutputToContain('app.title')
        ->assertSuccessful();
});

it('detects usage from blade lang directives', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['title' => 'x'],
    ]));
    config()->set('localization-i18n.scan_paths', [
        makeSourceDir(['view.blade.php' => "@lang('app.title')"]),
    ]);

    $this->artisan('translations:unused')
        ->doesntExpectOutputToContain('app.title')
        ->assertSuccessful();
});

it('detects unprefixed t() calls against the default namespace', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['title' => 'x'],
    ]));
    config()->set('localization-i18n.frontend.default_namespace', 'app');
    config()->set('localization-i18n.scan_paths', [
        makeSourceDir(['Page.tsx' => "const x = t('title');"]),
    ]);

    $this->artisan('translations:unused')
        ->doesntExpectOutputToContain('app.title')
        ->assertSuccessful();
});

it('reports success output when every key is used', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['title' => 'x'],
    ]));
    config()->set('localization-i18n.scan_paths', [
        makeSourceDir(['Service.php' => "<?php trans('app.title');"]),
    ]);

    $this->artisan('translations:unused')
        ->expectsOutputToContain('No unused translation keys')
        ->assertSuccessful();
});
