<?php

namespace Scrapkit\LocalizationI18n\Analysis;

use Illuminate\Filesystem\Filesystem;

class UnusedKeysScanner
{
    /**
     * Matches __('key'), trans('key'), trans_choice('key', ...), the
     * "at-lang('key')" Blade directive and t('key') / t('ns:key') usages.
     */
    protected const USAGE_PATTERN = '/\b(?:__|trans_choice|trans|lang|t)\(\s*[\'"]([^\'"]+)[\'"]/';

    protected const SCANNED_EXTENSIONS = ['php', 'ts', 'tsx', 'js', 'jsx', 'vue'];

    public function __construct(protected Filesystem $files) {}

    /**
     * Defined keys never referenced in the scanned paths. Detection is
     * static and heuristic: dynamically built keys are not recognised,
     * so results are meant for review, never for automatic deletion.
     *
     * @param  list<string>  $definedKeys  flattened "namespace.key" strings
     * @param  list<string>  $scanPaths  absolute directories to scan
     * @return list<string>
     */
    public function unusedKeys(array $definedKeys, array $scanPaths, string $defaultNamespace = 'common'): array
    {
        $used = $this->usedKeys($scanPaths, $defaultNamespace);

        return array_values(array_filter(
            $definedKeys,
            fn (string $key): bool => ! in_array($key, $used, true)
        ));
    }

    /**
     * @param  list<string>  $scanPaths
     * @return list<string>
     */
    protected function usedKeys(array $scanPaths, string $defaultNamespace): array
    {
        $used = [];

        foreach ($scanPaths as $path) {
            if (! $this->files->isDirectory($path)) {
                continue;
            }

            foreach ($this->files->allFiles($path) as $file) {
                if (! in_array($file->getExtension(), self::SCANNED_EXTENSIONS, true)) {
                    continue;
                }

                preg_match_all(self::USAGE_PATTERN, $file->getContents(), $matches);

                foreach ($matches[1] as $key) {
                    // i18next "ns:key" and Laravel "ns.key" flatten alike.
                    $used[] = str_replace(':', '.', $key);

                    // An unprefixed i18next key targets the default namespace.
                    if (! str_contains($key, ':') && ! str_contains($key, '.')) {
                        $used[] = "{$defaultNamespace}.{$key}";
                    }
                }
            }
        }

        return array_values(array_unique($used));
    }
}
