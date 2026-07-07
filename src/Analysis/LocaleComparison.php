<?php

namespace Scrapkit\LocalizationI18n\Analysis;

final class LocaleComparison
{
    /**
     * @param  list<string>  $missing  keys the reference locale has and this locale lacks
     * @param  list<string>  $extra  keys this locale has and the reference locale lacks
     */
    public function __construct(
        public readonly string $locale,
        public readonly array $missing,
        public readonly array $extra,
    ) {}

    public function inSync(): bool
    {
        return $this->missing === [] && $this->extra === [];
    }
}
