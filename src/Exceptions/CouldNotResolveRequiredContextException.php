<?php

declare(strict_types=1);

namespace Honeystone\Context\Exceptions;

use RuntimeException;

final class CouldNotResolveRequiredContextException extends RuntimeException
{
    public function __construct(private readonly string $name, ?string $bound = null)
    {
        parent::__construct(
            "The {$name} context is required".
            ($bound !== null ? " when {$bound} is provided" : '').
            ', but could not be resolved.'
        );
    }

    public function getName(): string
    {
        return $this->name;
    }
}
