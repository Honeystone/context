<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\ContextResolver;

class AuthenticatedProjectResolver extends ContextResolver
{
    public function resolveUser(): User
    {
        return new User();
    }

    public function resolveTeam(): Team
    {
        return new Team();
    }

    public function resolveProject(): Project
    {
        return new Project();
    }

    public static function deserialize(array $data): self
    {
        return new static();
    }
}
