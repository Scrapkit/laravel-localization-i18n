<?php

use Scrapkit\LocalizationI18n\Export\PluralConverter;

beforeEach(function () {
    $this->converter = new PluralConverter;
});

it('passes through values without plural forms', function () {
    $conversion = $this->converter->convert('title', 'Utenti');

    expect($conversion->entries)->toBe(['title' => 'Utenti'])
        ->and($conversion->warning)->toBeNull();
});

it('expands a simple two-form plural into i18next suffixed keys', function () {
    $conversion = $this->converter->convert('apples', 'mela|mele');

    expect($conversion->entries)->toBe([
        'apples_one' => 'mela',
        'apples_other' => 'mele',
    ])->and($conversion->warning)->toBeNull();
});

it('keeps range plural syntax as-is with a warning', function () {
    $conversion = $this->converter->convert('range', '{0} niente|[1,*] qualcosa');

    expect($conversion->entries)->toBe(['range' => '{0} niente|[1,*] qualcosa'])
        ->and($conversion->warning)->not->toBeNull();
});

it('keeps plurals with more than two forms as-is with a warning', function () {
    $conversion = $this->converter->convert('many', 'uno|due|tre');

    expect($conversion->entries)->toBe(['many' => 'uno|due|tre'])
        ->and($conversion->warning)->not->toBeNull();
});
