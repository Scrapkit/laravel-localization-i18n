<?php

namespace Scrapkit\LocalizationI18n\Analysis;

use Illuminate\Filesystem\Filesystem;

class TranslationSet
{
    public function __construct(protected Filesystem $files) {}

    /**
     * Every translation key of a locale, flattened to
     * "namespace.dot.path" strings.
     *
     * @return list<string>
     */
    public function keys(string $langPath, string $locale): array
    {
        $directory = "{$langPath}/{$locale}";

        if (! $this->files->isDirectory($directory)) {
            return [];
        }

        $keys = [];

        foreach ($this->files->files($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $translations = $this->files->getRequire($file->getPathname());

            if (! is_array($translations)) {
                continue;
            }

            $keys = array_merge(
                $keys,
                $this->flatten($translations, $file->getFilenameWithoutExtension().'.')
            );
        }

        sort($keys);

        return $keys;
    }

    /**
     * @param  array<array-key, mixed>  $group
     * @return list<string>
     */
    protected function flatten(array $group, string $prefix): array
    {
        $keys = [];

        foreach ($group as $key => $value) {
            if (is_array($value)) {
                $keys = array_merge($keys, $this->flatten($value, "{$prefix}{$key}."));

                continue;
            }

            $keys[] = "{$prefix}{$key}";
        }

        return $keys;
    }
}
