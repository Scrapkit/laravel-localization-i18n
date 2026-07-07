<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->output = sys_get_temp_dir().'/i18n-output-'.uniqid();
    config()->set('localization-i18n.frontend.output_path', $this->output);
    $this->app->useLangPath(__DIR__.'/TestSupport/lang');
});

afterEach(function () {
    (new Filesystem)->deleteDirectory($this->output);
});

it('generates json for every supported locale and discovered namespace', function () {
    $this->artisan('translations:generate')->assertSuccessful();

    expect(file_exists($this->output.'/it/common.json'))->toBeTrue()
        ->and(file_exists($this->output.'/en/common.json'))->toBeTrue()
        ->and(file_exists($this->output.'/it/users.json'))->toBeTrue()
        ->and(file_exists($this->output.'/en/users.json'))->toBeTrue();

    $common = json_decode(file_get_contents($this->output.'/it/common.json'), true);

    expect($common['greeting'])->toBe('Ciao {{name}}')
        ->and($common['apples_one'])->toBe('mela')
        ->and($common['apples_other'])->toBe('mele')
        ->and($common['nav'])->toBe(['home' => 'Pagina iniziale', 'users' => 'Utenti']);
});

it('excludes configured namespaces from the export', function () {
    $this->artisan('translations:generate')->assertSuccessful();

    expect(file_exists($this->output.'/it/validation.json'))->toBeFalse()
        ->and(file_exists($this->output.'/en/validation.json'))->toBeFalse();
});

it('honours an explicit namespace whitelist', function () {
    config()->set('localization-i18n.frontend.namespaces', ['users']);

    $this->artisan('translations:generate')->assertSuccessful();

    expect(file_exists($this->output.'/it/users.json'))->toBeTrue()
        ->and(file_exists($this->output.'/it/common.json'))->toBeFalse();
});

it('warns about namespaces missing for a locale', function () {
    config()->set('localization-i18n.frontend.namespaces', ['common', 'ghost']);

    $this->artisan('translations:generate')
        ->expectsOutputToContain('ghost')
        ->assertSuccessful();

    expect(file_exists($this->output.'/it/common.json'))->toBeTrue();
});

it('writes the shared config json', function () {
    $this->artisan('translations:generate')->assertSuccessful();

    $config = json_decode(file_get_contents($this->output.'/config.json'), true);

    expect($config['locales'])->toBe(['it', 'en'])
        ->and($config['default'])->toBe('it')
        ->and($config['fallback'])->toBe('en')
        ->and($config['defaultNamespace'])->toBe('common');
});

it('writes typescript resource types built from the default locale', function () {
    $this->artisan('translations:generate')->assertSuccessful();

    $types = file_get_contents($this->output.'/resources.d.ts');

    expect($types)->toContain("defaultNS: 'common';")
        ->toContain("'users': typeof import('./it/users.json');");
});

it('skips the typescript types when disabled', function () {
    config()->set('localization-i18n.frontend.generate_types', false);

    $this->artisan('translations:generate')->assertSuccessful();

    expect(file_exists($this->output.'/resources.d.ts'))->toBeFalse();
});

it('reports drift with --check without touching the files', function () {
    $this->artisan('translations:generate')->assertSuccessful();

    file_put_contents($this->output.'/it/common.json', '{}');

    $this->artisan('translations:generate', ['--check' => true])->assertFailed();

    expect(file_get_contents($this->output.'/it/common.json'))->toBe('{}');
});

it('passes the check when the output is current', function () {
    $this->artisan('translations:generate')->assertSuccessful();

    $this->artisan('translations:generate', ['--check' => true])->assertSuccessful();
});
