<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\ContextResolver;
use UnexpectedValueException;

use function Honeystone\Context\context;

class InitalizationProbeResolver extends ContextResolver
{
    public function resolveUser(): ?User
    {
        if (context()->initalized()) {
            throw new UnexpectedValueException('Context is initalized!');
        }

        return null;
    }

    public static function deserialize(array $data): static
    {
        return new static;
    }
}
