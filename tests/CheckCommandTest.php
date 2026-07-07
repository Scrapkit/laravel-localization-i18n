<?php

it('passes when all locales share the same keys', function () {
    $this->app->useLangPath(__DIR__.'/TestSupport/lang');

    $this->artisan('translations:check')->assertSuccessful();
});

it('fails when a locale misses keys from the reference locale', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['a' => 'x', 'b' => 'y'],
        'en/app.php' => ['a' => 'x'],
    ]));

    $this->artisan('translations:check')
        ->expectsOutputToContain('app.b')
        ->assertFailed();
});

it('fails when a locale has keys the reference locale misses', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['a' => 'x'],
        'en/app.php' => ['a' => 'x', 'stray' => 'y'],
    ]));

    $this->artisan('translations:check')
        ->expectsOutputToContain('app.stray')
        ->assertFailed();
});

it('reports nested keys in dot notation', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['group' => ['inner' => 'x']],
        'en/app.php' => ['group' => []],
    ]));

    $this->artisan('translations:check')
        ->expectsOutputToContain('app.group.inner')
        ->assertFailed();
});

it('fails when a locale misses an entire namespace file', function () {
    $this->app->useLangPath(makeLangDir([
        'it/app.php' => ['a' => 'x'],
        'it/other.php' => ['b' => 'y'],
        'en/app.php' => ['a' => 'x'],
    ]));

    $this->artisan('translations:check')
        ->expectsOutputToContain('other.b')
        ->assertFailed();
});
