<?php

declare(strict_types=1);

namespace Honeystone\Context\Exceptions;

use RuntimeException;

final class ContextIntegrityException extends RuntimeException
{
    public function __construct(private readonly string $name)
    {
        parent::__construct("The context integrity could not be verified at the {$name} context.");
    }

    public function getName(): string
    {
        return $this->name;
    }
}
