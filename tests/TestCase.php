<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests;

use Illuminate\Support\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Honeystone\Context\Providers\ContextServiceProvider;

class TestCase extends Orchestra
{
    /**
     * @return array<class-string<ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [ContextServiceProvider::class,];
    }
}
