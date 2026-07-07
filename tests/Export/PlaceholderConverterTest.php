<?php

use Scrapkit\LocalizationI18n\Export\PlaceholderConverter;

beforeEach(function () {
    $this->converter = new PlaceholderConverter;
});

it('converts a laravel placeholder to i18next interpolation', function () {
    expect($this->converter->convert('Ciao :name'))->toBe('Ciao {{name}}');
});

it('normalizes ucfirst placeholders', function () {
    expect($this->converter->convert('Ciao :Name'))->toBe('Ciao {{name}}');
});

it('normalizes all-caps placeholders', function () {
    expect($this->converter->convert('Ciao :NAME'))->toBe('Ciao {{name}}');
});

it('preserves camelCase placeholder names', function () {
    expect($this->converter->convert('Ciao :userName'))->toBe('Ciao {{userName}}');
});

it('converts multiple placeholders in one value', function () {
    expect($this->converter->convert(':count utenti su :total'))
        ->toBe('{{count}} utenti su {{total}}');
});

it('ignores colons not followed by an identifier', function () {
    expect($this->converter->convert('Alle 10:30 vai su https://example.com'))
        ->toBe('Alle 10:30 vai su https://example.com');
});
