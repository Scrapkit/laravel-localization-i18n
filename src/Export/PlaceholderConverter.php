<?php

namespace Scrapkit\LocalizationI18n\Export;

class PlaceholderConverter
{
    /**
     * Convert Laravel ":placeholder" syntax to i18next "{{placeholder}}"
     * interpolation. Laravel's ":Name" / ":NAME" casing variants map to
     * the same replacement key, so they are normalized to it; the case
     * transformation they imply has no i18next equivalent.
     */
    public function convert(string $value): string
    {
        return (string) preg_replace_callback(
            '/:([A-Za-z_][A-Za-z0-9_]*)/',
            fn (array $matches): string => '{{'.$this->normalizeName($matches[1]).'}}',
            $value
        );
    }

    protected function normalizeName(string $name): string
    {
        if (strlen($name) > 1 && $name === strtoupper($name)) {
            return strtolower($name);
        }

        return lcfirst($name);
    }
}
