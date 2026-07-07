<?php

namespace Scrapkit\LocalizationI18n\Tests;

use Inertia\Inertia;

class InertiaShareDisabledTest extends TestCase
{
    protected function defineEnvironment($app)
    {
        $app['config']->set('localization-i18n.inertia.share', false);
    }

    public function test_it_does_not_share_localization_data_when_disabled(): void
    {
        $this->assertNull(Inertia::getShared('localization'));
    }
}
