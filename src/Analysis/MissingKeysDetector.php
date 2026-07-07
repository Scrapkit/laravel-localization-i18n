<?php

namespace Scrapkit\LocalizationI18n\Analysis;

class MissingKeysDetector
{
    public function __construct(protected TranslationSet $translations) {}

    /**
     * Compare every locale against the reference locale's key set.
     *
     * @param  list<string>  $locales
     * @return list<LocaleComparison>
     */
    public function compare(string $langPath, string $reference, array $locales): array
    {
        $referenceKeys = $this->translations->keys($langPath, $reference);

        $comparisons = [];

        foreach ($locales as $locale) {
            if ($locale === $reference) {
                continue;
            }

            $localeKeys = $this->translations->keys($langPath, $locale);

            $comparisons[] = new LocaleComparison(
                $locale,
                array_values(array_diff($referenceKeys, $localeKeys)),
                array_values(array_diff($localeKeys, $referenceKeys)),
            );
        }

        return $comparisons;
    }
}
