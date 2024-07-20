<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

use Closure;

interface InitializesTemporaryContext extends InitializesContext
{
    /**
     * Run the application under this temporary context.
     */
    public function start(): ManagesContext;

    /**
     * Stop running the application under this temporary context.
     */
    public function end(): ManagesContext;

    /**
     * Run the closure with the application context temporarily switched.
     */
    public function run(Closure $closure): mixed;
}
