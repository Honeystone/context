<?php

declare(strict_types=1);

namespace Honeystone\Context\Exceptions;

use Honeystone\Context\Contracts\ManagesContext;
use RuntimeException;

final class ContextAlreadyInitializedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'The application context has already been initialized. If you need to re-initialize the context use `'.
            ManagesContext::class.'::reinitialize()`.'
        );
    }
}
