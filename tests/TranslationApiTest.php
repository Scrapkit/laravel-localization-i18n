<?php

namespace Scrapkit\LocalizationI18n\Tests;

class TranslationApiTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('localization-i18n.api.enabled', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->useLangPath(__DIR__.'/TestSupport/lang');
    }

    public function test_it_returns_a_namespace_for_a_locale(): void
    {
        $this->getJson('/api/translations/it/common')
            ->assertOk()
            ->assertJsonPath('greeting', 'Ciao {{name}}')
            ->assertJsonPath('nav.home', 'Pagina iniziale');
    }

    public function test_it_returns_every_namespace_when_none_is_given(): void
    {
        $this->getJson('/api/translations/it')
            ->assertOk()
            ->assertJsonPath('common.greeting', 'Ciao {{name}}')
            ->assertJsonPath('users.title', 'Gestione utenti');
    }

    public function test_it_rejects_an_unsupported_locale(): void
    {
        $this->getJson('/api/translations/de/common')->assertNotFound();
    }

    public function test_it_rejects_an_unknown_namespace(): void
    {
        $this->getJson('/api/translations/it/ghost')->assertNotFound();
    }

    public function test_it_does_not_expose_excluded_namespaces(): void
    {
        $this->getJson('/api/translations/it/validation')->assertNotFound();
    }

    public function test_it_sends_an_etag_and_honours_if_none_match(): void
    {
        $first = $this->getJson('/api/translations/it/common')->assertOk();

        $etag = $first->headers->get('ETag');

        $this->assertNotNull($etag);

        $this->getJson('/api/translations/it/common', ['If-None-Match' => $etag])
            ->assertStatus(304);
    }
}
