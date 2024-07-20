<?php

declare(strict_types=1);

namespace Honeystone\Context\Exceptions;

use RuntimeException;

final class NullResolvedContextException extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct("The {$name} context has was resolved, but was null.");
    }
}
