<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests;

use Honeystone\Context\Providers\ContextServiceProvider;
use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @return array<class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [ContextServiceProvider::class];
    }
}
