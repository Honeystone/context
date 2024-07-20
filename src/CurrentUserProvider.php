<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Honeystone\Context\Contracts\ProvidesCurrentUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory;

final class CurrentUserProvider implements ProvidesCurrentUser
{
    public function __construct(private readonly Factory $factory)
    {
    }

    public function isGuest(): bool
    {
        return $this->factory->guard()->guest();
    }

    public function isAuthenticated(): bool
    {
        return $this->factory->guard()->check();
    }

    public function getUser(): Authenticatable
    {
        return $this->factory->guard()->user();
    }

    public function serialize(): ?int
    {
        return $this->isAuthenticated() ? $this->getUser()->getAuthIdentifier() : null;
    }

    public function deserialize(?int $id): self
    {
        if ($id === null) {
            $this->factory->guard()->logout();
            return $this;
        }

        if ($this->isGuest() || $id !== $this->getUser()->getAuthIdentifier()) {
            $this->factory->guard()->loginUsingId($id);
        }

        return $this;
    }
}
