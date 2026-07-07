<?php

namespace Scrapkit\LocalizationI18n\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Scrapkit\LocalizationI18n\LocaleManager;

class LocaleController
{
    public function __invoke(Request $request, LocaleManager $manager): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($manager->supportedLocales())],
        ]);

        $manager->apply($validated['locale'], $request);

        return back();
    }
}
