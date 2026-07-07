<?php

namespace Scrapkit\LocalizationI18n\Commands;

use Illuminate\Console\Command;
use Scrapkit\LocalizationI18n\Analysis\TranslationSet;
use Scrapkit\LocalizationI18n\Analysis\UnusedKeysScanner;
use Scrapkit\LocalizationI18n\LocaleManager;

class UnusedCommand extends Command
{
    protected $signature = 'translations:unused';

    protected $description = 'Report translation keys never referenced in the scanned source paths (heuristic, report-only)';

    public function handle(TranslationSet $translations, UnusedKeysScanner $scanner, LocaleManager $locales): int
    {
        $defined = $translations->keys($this->laravel->langPath(), $locales->defaultLocale());

        $paths = array_map(
            fn (string $path): string => str_starts_with($path, DIRECTORY_SEPARATOR) ? $path : base_path($path),
            (array) config('localization-i18n.scan_paths', []),
        );

        $unused = $scanner->unusedKeys(
            $defined,
            array_values($paths),
            (string) config('localization-i18n.frontend.default_namespace', 'common'),
        );

        if ($unused === []) {
            $this->components->info('No unused translation keys detected.');

            return self::SUCCESS;
        }

        $this->components->warn(sprintf(
            '%d translation key(s) appear to be unused. Review before deleting: dynamically built keys cannot be detected.',
            count($unused),
        ));
        $this->components->bulletList($unused);

        return self::SUCCESS;
    }
}
