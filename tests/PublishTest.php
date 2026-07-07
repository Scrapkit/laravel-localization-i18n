<?php

use Illuminate\Filesystem\Filesystem;

it('publishes the frontend stubs', function () {
    $target = resource_path('js/i18n');
    $files = new Filesystem;
    $files->deleteDirectory($target);

    $this->artisan('vendor:publish', ['--tag' => 'localization-i18n-frontend'])
        ->assertSuccessful();

    expect(file_exists("{$target}/index.ts"))->toBeTrue()
        ->and(file_exists("{$target}/backend.ts"))->toBeTrue()
        ->and(file_exists("{$target}/use-locale.ts"))->toBeTrue();

    $files->deleteDirectory($target);
});

it('publishes the starter translations into the application lang path', function () {
    $langPath = $this->app->langPath();
    $files = new Filesystem;
    $files->delete(["{$langPath}/it/common.php", "{$langPath}/en/common.php"]);

    $this->artisan('vendor:publish', ['--tag' => 'localization-i18n-translations'])
        ->assertSuccessful();

    expect(file_exists("{$langPath}/it/common.php"))->toBeTrue()
        ->and(file_exists("{$langPath}/en/common.php"))->toBeTrue();

    $files->delete(["{$langPath}/it/common.php", "{$langPath}/en/common.php"]);
});
