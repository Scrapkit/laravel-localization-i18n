<?php

namespace Scrapkit\LocalizationI18n\Export;

final class PluralConversion
{
    /**
     * @param  array<string, string>  $entries
     */
    public function __construct(
        public readonly array $entries,
        public readonly ?string $warning = null,
    ) {}
}
