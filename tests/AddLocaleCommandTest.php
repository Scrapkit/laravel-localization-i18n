<?php

it('scaffolds a new locale from the default locale files', function () {
    $langPath = makeLangDir([
        'it/app.php' => ['a' => 'x'],
        'it/other.php' => ['b' => 'y'],
    ]);
    $this->app->useLangPath($langPath);

    $this->artisan('translations:add-locale', ['locale' => 'fr'])
        ->expectsOutputToContain('supported_locales')
        ->assertSuccessful();

    expect(file_exists("{$langPath}/fr/app.php"))->toBeTrue()
        ->and(file_exists("{$langPath}/fr/other.php"))->toBeTrue()
        ->and(require "{$langPath}/fr/app.php")->toBe(['a' => 'x']);
});

it('does not overwrite existing files of the new locale', function () {
    $langPath = makeLangDir([
        'it/app.php' => ['a' => 'x'],
        'fr/app.php' => ['a' => 'déjà traduit'],
    ]);
    $this->app->useLangPath($langPath);

    $this->artisan('translations:add-locale', ['locale' => 'fr'])->assertSuccessful();

    expect(require "{$langPath}/fr/app.php")->toBe(['a' => 'déjà traduit']);
});

it('rejects an invalid locale code', function () {
    $this->app->useLangPath(makeLangDir(['it/app.php' => ['a' => 'x']]));

    $this->artisan('translations:add-locale', ['locale' => 'not a locale!'])
        ->assertFailed();
});
