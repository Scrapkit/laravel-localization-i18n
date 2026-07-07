<?php

use Scrapkit\LocalizationI18n\Export\TypeScriptTypesGenerator;

it('generates an i18next module augmentation for the default locale', function () {
    $types = (new TypeScriptTypesGenerator)->generate(['common', 'users'], 'it', 'common');

    expect($types)->toContain("declare module 'i18next'")
        ->toContain("defaultNS: 'common';")
        ->toContain("'common': typeof import('./it/common.json');")
        ->toContain("'users': typeof import('./it/users.json');");
});
