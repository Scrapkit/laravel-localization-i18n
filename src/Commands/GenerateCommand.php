<?php

namespace Scrapkit\LocalizationI18n\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Scrapkit\LocalizationI18n\Export\FrontendNamespaces;
use Scrapkit\LocalizationI18n\Export\TranslationExporter;
use Scrapkit\LocalizationI18n\Export\TypeScriptTypesGenerator;
use Scrapkit\LocalizationI18n\LocaleManager;

class GenerateCommand extends Command
{
    protected $signature = 'translations:generate
        {--check : Fail when the generated files are out of date instead of writing them}';

    protected $description = 'Export PHP translation files to i18next JSON, shared config and TypeScript types';

    public function handle(
        Filesystem $files,
        FrontendNamespaces $frontendNamespaces,
        TranslationExporter $exporter,
        TypeScriptTypesGenerator $types,
        LocaleManager $locales,
    ): int {
        $langPath = $this->laravel->langPath();
        $outputPath = (string) config('localization-i18n.frontend.output_path');
        $namespaces = $frontendNamespaces->all($langPath, $locales->defaultLocale());

        $outputs = [];

        foreach ($locales->supportedLocales() as $locale) {
            foreach ($namespaces as $namespace) {
                $source = "{$langPath}/{$locale}/{$namespace}.php";

                if (! $files->exists($source)) {
                    $this->components->warn("Missing translation file for [{$locale}/{$namespace}]: {$source}");

                    continue;
                }

                $result = $exporter->export($source);

                foreach ($result->warnings as $warning) {
                    $this->components->warn("[{$locale}/{$namespace}] {$warning}");
                }

                $outputs["{$locale}/{$namespace}.json"] = $this->encode($result->translations);
            }
        }

        $outputs['config.json'] = $this->encode([
            'default' => $locales->defaultLocale(),
            'defaultNamespace' => $this->defaultNamespace(),
            'fallback' => $locales->fallbackLocale(),
            'locales' => $locales->supportedLocales(),
        ]);

        if (config('localization-i18n.frontend.generate_types')) {
            $outputs['resources.d.ts'] = $types->generate(
                $namespaces,
                $locales->defaultLocale(),
                $this->defaultNamespace(),
            );
        }

        if ($this->option('check')) {
            return $this->check($files, $outputPath, $outputs);
        }

        foreach ($outputs as $relative => $content) {
            $target = "{$outputPath}/{$relative}";

            $files->ensureDirectoryExists(dirname($target));
            $files->put($target, $content);
        }

        $this->components->info(sprintf('Generated %d translation file(s) in %s.', count($outputs), $outputPath));

        return self::SUCCESS;
    }

    protected function defaultNamespace(): string
    {
        return (string) config('localization-i18n.frontend.default_namespace', 'common');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function encode(array $data): string
    {
        return json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        )."\n";
    }

    /**
     * @param  array<string, string>  $outputs
     */
    protected function check(Filesystem $files, string $outputPath, array $outputs): int
    {
        $stale = [];

        foreach ($outputs as $relative => $content) {
            $target = "{$outputPath}/{$relative}";

            if (! $files->exists($target) || $files->get($target) !== $content) {
                $stale[] = $relative;
            }
        }

        if ($stale !== []) {
            $this->components->error('Generated translations are out of date. Run [translations:generate].');
            $this->components->bulletList($stale);

            return self::FAILURE;
        }

        $this->components->info('Generated translations are up to date.');

        return self::SUCCESS;
    }
}
