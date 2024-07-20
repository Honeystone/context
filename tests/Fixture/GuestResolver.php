<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\ContextResolver;

class GuestResolver extends ContextResolver
{
    public function resolveUser(): ?User
    {
        return null;
    }

    public static function deserialize(array $data): static
    {
        return new static();
    }
}
