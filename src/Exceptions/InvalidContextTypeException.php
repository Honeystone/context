<?php

declare(strict_types=1);

namespace Honeystone\Context\Exceptions;

use RuntimeException;

final class InvalidContextTypeException extends RuntimeException
{
    public function __construct(string $name, string $expected, string $actual)
    {
        parent::__construct(
            "The {$name} context is expected to be of the type `{$expected}`, but `{$actual}` was provided."
        );
    }
}
