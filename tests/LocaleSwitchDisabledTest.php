<?php

namespace Scrapkit\LocalizationI18n\Tests;

class LocaleSwitchDisabledTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('localization-i18n.routes.switch_enabled', false);
    }

    public function test_the_switch_route_is_not_registered_when_disabled(): void
    {
        $this->put('/locale', ['locale' => 'en'])->assertNotFound();
    }
}
