<?php

namespace Scrapkit\LocalizationI18n\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Scrapkit\LocalizationI18n\Export\FrontendNamespaces;
use Scrapkit\LocalizationI18n\Export\TranslationExporter;
use Scrapkit\LocalizationI18n\LocaleManager;

class TranslationController
{
    public function __construct(
        protected Filesystem $files,
        protected FrontendNamespaces $namespaces,
        protected TranslationExporter $exporter,
        protected LocaleManager $locales,
    ) {}

    public function __invoke(Request $request, string $locale, ?string $namespace = null): JsonResponse
    {
        abort_unless($this->locales->isSupported($locale), 404);

        $langPath = app()->langPath();
        $available = $this->namespaces->all($langPath, $this->locales->defaultLocale());

        if ($namespace !== null) {
            abort_unless(in_array($namespace, $available, true), 404);

            $payload = $this->exportNamespace($langPath, $locale, $namespace);
        } else {
            $payload = [];

            foreach ($available as $availableNamespace) {
                $payload[$availableNamespace] = $this->exportNamespace($langPath, $locale, $availableNamespace);
            }
        }

        $response = response()->json($payload === [] ? (object) [] : $payload)
            ->setEtag(sha1((string) json_encode($payload)));

        $response->isNotModified($request);

        return $response;
    }

    /**
     * @return array<string, mixed>
     */
    protected function exportNamespace(string $langPath, string $locale, string $namespace): array
    {
        $source = "{$langPath}/{$locale}/{$namespace}.php";

        if (! $this->files->exists($source)) {
            return [];
        }

        return $this->exporter->export($source)->translations;
    }
}
