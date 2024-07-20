<?php

declare(strict_types=1);

namespace Honeystone\Context\Providers;

use Honeystone\Context\ContextManager;
use Honeystone\Context\Contracts\ManagesContext;
use Honeystone\Context\CurrentUserProvider;
use Honeystone\Context\Queue\Listeners\InitializeContext;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Testing\Fakes\QueueFake;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function app;
use function dirname;
use function Honeystone\Context\context;

final class ContextServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('honeystone-context')
            ->setBasePath(dirname(__DIR__));
    }

    public function packageRegistered(): void
    {
        $this->registerContextManager();

        $this->registerQueuePayloadGenerator();
        $this->registerQueueListener();
    }

    private function registerContextManager(): void
    {
        app()->singleton(ManagesContext::class, static function (): ManagesContext {
            return new ContextManager(new CurrentUserProvider(app(AuthFactory::class)));
        });
    }

    private function registerQueuePayloadGenerator(): void
    {
        $this->booting(static function (): void {

            $queue = app(QueueManager::class);

            if (!$queue instanceof QueueFake) {
                $queue->createPayloadUsing(static fn (): array => [
                    'honeystone-context' => context()->initialized() ? context()->serialize() : null,
                ]);
            }
        });
    }

    private function registerQueueListener(): void
    {
        $this->booting(static function (): void {
            Event::listen(JobProcessing::class, InitializeContext::class);
        });
    }
}
