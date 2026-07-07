<?php

use Illuminate\Filesystem\Filesystem;
use Scrapkit\LocalizationI18n\Export\PlaceholderConverter;
use Scrapkit\LocalizationI18n\Export\PluralConverter;
use Scrapkit\LocalizationI18n\Export\TranslationExporter;

beforeEach(function () {
    $this->exporter = new TranslationExporter(
        new Filesystem,
        new PlaceholderConverter,
        new PluralConverter,
    );
});

it('exports a namespace with converted values and sorted keys', function () {
    $result = $this->exporter->export(__DIR__.'/../TestSupport/lang/it/common.php');

    expect(array_keys($result->translations))
        ->toBe(['apples_one', 'apples_other', 'greeting', 'nav', 'range'])
        ->and($result->translations['greeting'])->toBe('Ciao {{name}}')
        ->and($result->translations['apples_one'])->toBe('mela')
        ->and($result->translations['apples_other'])->toBe('mele');
});

it('preserves nested groups', function () {
    $result = $this->exporter->export(__DIR__.'/../TestSupport/lang/it/common.php');

    expect($result->translations['nav'])->toBe([
        'home' => 'Pagina iniziale',
        'users' => 'Utenti',
    ]);
});

it('collects warnings for values it cannot convert', function () {
    $result = $this->exporter->export(__DIR__.'/../TestSupport/lang/it/common.php');

    expect($result->warnings)->toHaveCount(1)
        ->and($result->warnings[0])->toContain('range');
});
