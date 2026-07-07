<?php

namespace Scrapkit\LocalizationI18n\Export;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

class TranslationExporter
{
    public function __construct(
        protected Filesystem $files,
        protected PlaceholderConverter $placeholders,
        protected PluralConverter $plurals,
    ) {}

    /**
     * Convert a Laravel translation file into an i18next-ready structure
     * with recursively sorted keys, so the output is deterministic and
     * diff-friendly.
     */
    public function export(string $path): ExportResult
    {
        $translations = $this->files->getRequire($path);

        if (! is_array($translations)) {
            throw new RuntimeException("Translation file [{$path}] must return an array.");
        }

        $warnings = [];
        $converted = $this->convertGroup($translations, '', $warnings);

        return new ExportResult($converted, $warnings);
    }

    /**
     * @param  array<array-key, mixed>  $group
     * @param  list<string>  $warnings
     * @return array<string, mixed>
     */
    protected function convertGroup(array $group, string $prefix, array &$warnings): array
    {
        $result = [];

        foreach ($group as $key => $value) {
            $key = (string) $key;

            if (is_array($value)) {
                $result[$key] = $this->convertGroup($value, "{$prefix}{$key}.", $warnings);

                continue;
            }

            $conversion = $this->plurals->convert($key, (string) $value);

            if ($conversion->warning !== null) {
                $warnings[] = "\"{$prefix}{$key}\" {$conversion->warning}";
            }

            foreach ($conversion->entries as $entryKey => $entryValue) {
                $result[$entryKey] = $this->placeholders->convert($entryValue);
            }
        }

        ksort($result);

        return $result;
    }
}
