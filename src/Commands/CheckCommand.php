<?php

namespace Scrapkit\LocalizationI18n\Commands;

use Illuminate\Console\Command;
use Scrapkit\LocalizationI18n\Analysis\MissingKeysDetector;
use Scrapkit\LocalizationI18n\LocaleManager;

class CheckCommand extends Command
{
    protected $signature = 'translations:check';

    protected $description = 'Verify that every supported locale defines the same translation keys as the default locale';

    public function handle(MissingKeysDetector $detector, LocaleManager $locales): int
    {
        $reference = $locales->defaultLocale();

        $comparisons = $detector->compare(
            $this->laravel->langPath(),
            $reference,
            $locales->supportedLocales(),
        );

        $inSync = true;

        foreach ($comparisons as $comparison) {
            if ($comparison->inSync()) {
                $this->components->twoColumnDetail("Locale [{$comparison->locale}]", 'in sync');

                continue;
            }

            $inSync = false;

            if ($comparison->missing !== []) {
                $this->components->error(sprintf(
                    'Locale [%s] is missing %d key(s) defined in [%s]:',
                    $comparison->locale,
                    count($comparison->missing),
                    $reference,
                ));
                $this->components->bulletList($comparison->missing);
            }

            if ($comparison->extra !== []) {
                $this->components->error(sprintf(
                    'Locale [%s] defines %d key(s) missing in [%s]:',
                    $comparison->locale,
                    count($comparison->extra),
                    $reference,
                ));
                $this->components->bulletList($comparison->extra);
            }
        }

        if (! $inSync) {
            return self::FAILURE;
        }

        $this->components->info("All locales are in sync with [{$reference}].");

        return self::SUCCESS;
    }
}
