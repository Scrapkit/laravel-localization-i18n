<?php

namespace Scrapkit\LocalizationI18n\Export;

class PluralConverter
{
    /**
     * Convert Laravel pipe plurals ("apple|apples") to i18next suffixed
     * keys ("key_one" / "key_other"). Range syntax ("{0} none|[1,*] some")
     * and plurals with more than two forms have no direct i18next
     * equivalent: they pass through unchanged with a warning.
     */
    public function convert(string $key, string $value): PluralConversion
    {
        if (! str_contains($value, '|')) {
            return new PluralConversion([$key => $value]);
        }

        if (preg_match('/[{\[]/', $value) === 1) {
            return new PluralConversion(
                [$key => $value],
                'uses Laravel range plural syntax, which i18next cannot interpret; exported as-is'
            );
        }

        $forms = explode('|', $value);

        if (count($forms) !== 2) {
            return new PluralConversion(
                [$key => $value],
                'has more than two plural forms; exported as-is'
            );
        }

        return new PluralConversion([
            "{$key}_one" => $forms[0],
            "{$key}_other" => $forms[1],
        ]);
    }
}
