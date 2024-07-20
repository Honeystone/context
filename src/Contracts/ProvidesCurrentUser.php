<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ProvidesCurrentUser
{
    public function isGuest(): bool;

    public function isAuthenticated(): bool;

    public function getUser(): Authenticatable;

    public function serialize(): ?int;

    /**
     * @return $this
     */
    public function deserialize(?int $id): self;
}
