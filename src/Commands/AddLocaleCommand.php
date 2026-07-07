<?php

namespace Scrapkit\LocalizationI18n\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Scrapkit\LocalizationI18n\LocaleManager;

class AddLocaleCommand extends Command
{
    protected $signature = 'translations:add-locale
        {locale : The locale code to scaffold (e.g. fr)}';

    protected $description = 'Scaffold translation files for a new locale by copying the default locale files';

    public function handle(Filesystem $files, LocaleManager $locales): int
    {
        $locale = (string) $this->argument('locale');

        if (preg_match('/^[a-z]{2,3}(?:[-_][A-Za-z]{2,4})?$/', $locale) !== 1) {
            $this->components->error("Invalid locale code [{$locale}].");

            return self::FAILURE;
        }

        $langPath = $this->laravel->langPath();
        $default = $locales->defaultLocale();
        $source = "{$langPath}/{$default}";

        if (! $files->isDirectory($source)) {
            $this->components->error("No translation files found for the default locale [{$default}] in {$langPath}.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists("{$langPath}/{$locale}");

        $copied = 0;

        foreach ($files->files($source) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $target = "{$langPath}/{$locale}/{$file->getFilename()}";

            if ($files->exists($target)) {
                $this->components->twoColumnDetail("{$locale}/{$file->getFilename()}", 'exists, skipped');

                continue;
            }

            $files->copy($file->getPathname(), $target);
            $copied++;
        }

        $this->components->info(sprintf(
            'Scaffolded %d file(s) for locale [%s] from [%s]. Translate them, then add "%s" to supported_locales in config/localization-i18n.php.',
            $copied,
            $locale,
            $default,
            $locale,
        ));

        return self::SUCCESS;
    }
}
