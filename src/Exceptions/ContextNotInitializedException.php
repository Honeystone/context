<?php

declare(strict_types=1);

namespace Honeystone\Context\Exceptions;

use Honeystone\Context\Contracts\ManagesContext;
use RuntimeException;

final class ContextNotInitializedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'The application context has not been initialized.  You should initialize the context using `'.
            ManagesContext::class.'::initialize()`.'
        );
    }
}
