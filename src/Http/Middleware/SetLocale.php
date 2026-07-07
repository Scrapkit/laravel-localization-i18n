<?php

namespace Scrapkit\LocalizationI18n\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\LocaleManager;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function __construct(protected LocaleManager $manager) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->manager->apply($this->manager->determine($request), $request);

        return $next($request);
    }
}
