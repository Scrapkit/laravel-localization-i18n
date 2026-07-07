<?php

use Illuminate\Filesystem\Filesystem;
use Scrapkit\LocalizationI18n\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Build a throwaway lang directory, e.g.:
 * makeLangDir(['it/app.php' => ['a' => 'x'], 'en/app.php' => ['a' => 'x']])
 *
 * @param  array<string, array<string, mixed>>  $tree
 */
function makeLangDir(array $tree): string
{
    $base = sys_get_temp_dir().'/lang-'.uniqid();
    $files = new Filesystem;

    foreach ($tree as $relative => $translations) {
        $path = "{$base}/{$relative}";
        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, '<?php return '.var_export($translations, true).';');
    }

    return $base;
}

/**
 * Build a throwaway source directory of files to scan, e.g.:
 * makeSourceDir(['Page.tsx' => "t('app:a')"])
 *
 * @param  array<string, string>  $tree
 */
function makeSourceDir(array $tree): string
{
    $base = sys_get_temp_dir().'/src-'.uniqid();
    $files = new Filesystem;

    foreach ($tree as $relative => $content) {
        $path = "{$base}/{$relative}";
        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, $content);
    }

    return $base;
}
