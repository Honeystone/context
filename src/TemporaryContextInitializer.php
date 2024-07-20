<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Closure;
use Honeystone\Context\Contracts\DefinesContext;
use Honeystone\Context\Contracts\InitializesContext;
use Honeystone\Context\Contracts\InitializesTemporaryContext;
use Honeystone\Context\Contracts\ManagesContext;
use Honeystone\Context\Contracts\ResolvesContext;

final class TemporaryContextInitializer implements InitializesTemporaryContext
{
    public function __construct(
        private readonly Closure $up,
        private readonly Closure $down,
        private readonly InitializesContext $initializer,
    ) {
    }

    public function start(): ManagesContext
    {
        return ($this->up)($this);
    }

    public function end(): ManagesContext
    {
        return ($this->down)($this);
    }

    public function run(Closure $closure): mixed
    {
        $this->start();

        try {
            $result = $closure();
        } finally {
            $this->end();
        }

        return $result;
    }

    public function resolve(ResolvesContext $resolver): self
    {
        $this->initializer->resolve($resolver);

        return $this;
    }

    public function hasResolved(string $name, bool $strict = true): bool
    {
        return $this->initializer->hasResolved($name, $strict);
    }

    public function getResolved(string $name, bool $strict = true): ?object
    {
        return $this->initializer->getResolved($name, $strict);
    }

    public function getAllResolved(): array
    {
        return $this->initializer->getAllResolved();
    }

    public function getDefinition(): DefinesContext
    {
        return $this->initializer->getDefinition();
    }

    public function serialize(): array
    {
        return $this->initializer->serialize();
    }

    public function deserialize(array $data): self
    {
        $this->initializer->deserialize($data);

        return $this;
    }
}
