<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\ContextResolver;

class InvalidUserResolver extends ContextResolver
{
    public function resolveUser(): User
    {
        return new User();
    }

    public function checkUser(): bool
    {
        return false;
    }

    public static function deserialize(array $data): static
    {
        return new static();
    }
}
