<?php

namespace Scrapkit\LocalizationI18n\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Scrapkit\LocalizationI18n\LocalizationI18nServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LocalizationI18nServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('session.driver', 'array');
    }
}
