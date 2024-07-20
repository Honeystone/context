<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\ContextResolver;

class ValidUserResolver extends ContextResolver
{
    public function resolveUser(): User
    {
        return new User();
    }

    public function checkUser(): bool
    {
        return true;
    }

    public static function deserialize(array $data): static
    {
        return new static();
    }
}
