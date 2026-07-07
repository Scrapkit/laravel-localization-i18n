<?php

it('merges the package configuration', function () {
    expect(config('localization-i18n.default_locale'))->toBe('it')
        ->and(config('localization-i18n.fallback_locale'))->toBe('en')
        ->and(config('localization-i18n.supported_locales'))->toBe(['it', 'en'])
        ->and(config('localization-i18n.api.enabled'))->toBeFalse();
});
