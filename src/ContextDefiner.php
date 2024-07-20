<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Generator;
use Honeystone\Context\Contracts\DefinesContext;

use function array_key_exists;
use function array_keys;
use function count;

final class ContextDefiner implements DefinesContext
{
    /**
     * @var array<string, class-string>
     */
    private array $contexts = [];

    /**
     * @var array<string, string|null>
     */
    private array $requiredWhen = [];

    /**
     * @var array<string, string|null>
     */
    private array $acceptedWhen = [];

    public function require(string $name, string $type): self
    {
        $this->contexts[$name] = $type;
        $this->requiredWhen[$name] = null;

        unset($this->acceptedWhen[$name]);

        return $this;
    }

    public function requireWhenProvided(string $provided, string $name, string $type): self
    {
        $this->contexts[$name] = $type;
        $this->requiredWhen[$name] = $provided;

        unset($this->acceptedWhen[$name]);

        return $this;
    }

    public function accept(string $name, string $type): self
    {
        $this->contexts[$name] = $type;
        $this->acceptedWhen[$name] = null;

        unset($this->requiredWhen[$name]);

        return $this;
    }

    public function acceptWhenProvided(string $provided, string $name, string $type): self
    {
        $this->contexts[$name] = $type;
        $this->acceptedWhen[$name] = $provided;

        unset($this->requiredWhen[$name]);

        return $this;
    }

    public function isRequired(string $name, ?string $bound = null): bool
    {
        return array_key_exists($name, $this->requiredWhen) && $this->requiredWhen[$name] === $bound;
    }

    public function isAccepted(string $name, ?string $bound = null): bool
    {
        if (array_key_exists($name, $this->requiredWhen)) {
            return true;
        }

        return array_key_exists($name, $this->acceptedWhen) && $this->acceptedWhen[$name] === $bound;
    }

    public function isDefined(string $name): bool
    {
        return array_key_exists($name, $this->contexts);
    }

    public function findBoundContext(string $name): ?string
    {
        if (array_key_exists($name, $this->requiredWhen)) {
            return $this->requiredWhen[$name];
        }

        if (array_key_exists($name, $this->acceptedWhen)) {
            return $this->acceptedWhen[$name];
        }

        return null;
    }

    public function getType(string $name): string
    {
        return $this->contexts[$name];
    }

    public function eachContext(): Generator
    {
        foreach (array_keys($this->contexts) as $name) {
            yield $name;
        }
    }

    public function hasDefinitions(): bool
    {
        return count($this->contexts) > 0;
    }

    public function serialize(): array
    {
        return [
            'contexts' => $this->contexts,
            'required_when' => $this->requiredWhen,
            'accepted_when' => $this->acceptedWhen,
        ];
    }

    public function deserialize(array $data): self {
        $this->contexts = $data['contexts'];
        $this->requiredWhen = $data['required_when'];
        $this->acceptedWhen = $data['accepted_when'];

        return $this;
    }
}
