<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\ContextResolver;
use RuntimeException;

use function array_key_exists;

class AuthenticatedTeamResolver extends ContextResolver
{
    public function resolveUser(): User
    {
        return new User();
    }

    public function resolveTeam(): Team
    {
        return new Team();
    }

    public function resolveProject(): ?Project
    {
        return null;
    }

    public function serializeUser(): int
    {
        return 1;
    }

    public function serializeTeam(): int
    {
        return 2;
    }

    public static function deserialize(array $data): static
    {
        if (
            $data['user'] === 1 &&
            $data['team'] === 2 &&
            !array_key_exists('project', $data)
        ) {
            return new static();
        }

        throw new RuntimeException('Failed to deserialize!');
    }
}
