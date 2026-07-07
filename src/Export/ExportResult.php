<?php

namespace Scrapkit\LocalizationI18n\Export;

final class ExportResult
{
    /**
     * @param  array<string, mixed>  $translations
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly array $translations,
        public readonly array $warnings = [],
    ) {}
}
