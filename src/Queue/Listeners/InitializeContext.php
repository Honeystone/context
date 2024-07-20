<?php

declare(strict_types=1);

namespace Honeystone\Context\Queue\Listeners;

use Illuminate\Queue\Events\JobProcessing;

use function Honeystone\Context\context;

final class InitializeContext
{
    public function handle(JobProcessing $event): void
    {
        $data = $event->job->payload()['honeystone-context'] ?? null;

        if ($data === null) {
            context()->deinitialize();
            return;
        }

        if ($data['key'] !== context()->getInitializationKey()) {
            context()->deserialize($data);
        }
    }
}
