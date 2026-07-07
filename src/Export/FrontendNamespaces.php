<?php

namespace Scrapkit\LocalizationI18n\Export;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class FrontendNamespaces
{
    public function __construct(
        protected Filesystem $files,
        protected Repository $config,
    ) {}

    /**
     * The namespaces exposed to the frontend: the configured whitelist,
     * or every translation file of the default locale minus the
     * exclusions.
     *
     * @return list<string>
     */
    public function all(string $langPath, string $defaultLocale): array
    {
        $configured = $this->config->get('localization-i18n.frontend.namespaces');

        if (is_array($configured)) {
            return array_values($configured);
        }

        $directory = "{$langPath}/{$defaultLocale}";

        if (! $this->files->isDirectory($directory)) {
            return [];
        }

        $excluded = (array) $this->config->get('localization-i18n.frontend.exclude', []);

        return collect($this->files->files($directory))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php')
            ->map(fn (SplFileInfo $file): string => $file->getFilenameWithoutExtension())
            ->reject(fn (string $namespace): bool => in_array($namespace, $excluded, true))
            ->sort()
            ->values()
            ->all();
    }
}
