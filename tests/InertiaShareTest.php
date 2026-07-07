<?php

use Inertia\Inertia;

it('shares localization data with inertia', function () {
    $shared = Inertia::getShared('localization');

    expect($shared)->toBeCallable();

    $this->app->setLocale('en');

    expect(value($shared))->toBe([
        'locale' => 'en',
        'locales' => ['it', 'en'],
    ]);
});
