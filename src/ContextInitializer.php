<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Honeystone\Context\Contracts\DefinesContext;
use Honeystone\Context\Contracts\InitializesContext;
use Honeystone\Context\Contracts\ManagesContext;
use Honeystone\Context\Contracts\ResolvesContext;
use Honeystone\Context\Exceptions\ContextAlreadyInitializedException;
use Honeystone\Context\Exceptions\ContextNotInitializedException;
use Honeystone\Context\Exceptions\CouldNotResolveRequiredContextException;
use Honeystone\Context\Exceptions\InvalidContextTypeException;
use Honeystone\Context\Exceptions\NullResolvedContextException;
use Honeystone\Context\Exceptions\UndefinedContextException;
use Illuminate\Support\Str;

use function array_key_exists;
use function get_class;
use function gettype;
use function in_array;
use function is_object;
use function method_exists;

final class ContextInitializer implements InitializesContext
{
    private ResolvesContext $resolver;

    private bool $initialized = false;

    /**
     * @var array<string>
     */
    private array $resolvable = [];

    /**
     * @var array<string, object|null>
     */
    private array $resolved = [];

    public function __construct(
        private readonly DefinesContext $definition,
        private readonly ManagesContext $manager,
    )
    {
    }

    public function resolve(ResolvesContext $resolver): self
    {
        if ($this->initialized) {
            throw new ContextAlreadyInitializedException();
        }

        $this->resolver = $resolver;

        $this->findResolvable();

        $this->resolveAccepted();

        $this->ensureRequirementsWereMet();

        $this->verifyContextIntegrity();

        $this->initialized = true;

        return $this;
    }

    public function hasResolved(string $name, bool $strict = true): bool
    {
        if (!$this->initialized) {
            throw new ContextNotInitializedException();
        }

        if (!array_key_exists($name, $this->resolved)) {
            return false;
        }

        if ($strict && $this->resolved[$name] === null) {
            return false;
        }

        return true;
    }

    public function getResolved(string $name, bool $strict = true): ?object
    {
        if (!$this->initialized) {
            throw new ContextNotInitializedException();
        }

        if (!$this->definition->isDefined($name)) {
            throw new UndefinedContextException($name);
        }

        if ($strict && !$this->hasResolved($name)) {
            throw new NullResolvedContextException($name);
        }

        return $this->resolved[$name];
    }

    public function getAllResolved(): array
    {
        if (!$this->initialized) {
            throw new ContextNotInitializedException();
        }

        return $this->resolved;
    }

    public function getDefinition(): DefinesContext
    {
        return $this->definition;
    }

    public function serialize(): array
    {
        if (!$this->initialized) {
            throw new ContextNotInitializedException();
        }

        return [
            'resolved' => $this->serializeResolved(),
            'resolver' => get_class($this->resolver),
            'definition' => $this->definition->serialize(),
        ];
    }

    public function deserialize(array $data): self
    {
        $this->definition->deserialize($data['definition']);
        $this->resolve($data['resolver']::deserialize($data['resolved']));

        return $this;
    }

    private function findResolvable(): void
    {
        foreach ($this->definition->eachContext() as $name) {

            if (method_exists($this->resolver, $this->getResolverMethodName($name))) {
                $this->resolvable[] = $name;
            }
        }
    }

    private function resolveAccepted(): void
    {
        foreach ($this->definition->eachContext() as $name) {

            if ($this->isResolvable($name)) {
                $this->resolved[$name] = $this->resolveContext($name);
            }
        }
    }

    private function isResolvable(string $name): bool
    {
        $bound = $this->definition->findBoundContext($name);

        if ($bound === null) {
            return true;
        }

        return in_array($bound, $this->resolvable);
    }

    private function resolveContext(string $name): ?object
    {
        $resolved = null;

        $method = $this->getResolverMethodName($name);

        if (method_exists($this->resolver, $method)) {
            $resolved = $this->resolver->$method($this->manager, $this->definition);
        }

        $type = $this->definition->getType($name);

        if ($resolved !== null && !$resolved instanceof $type) {
            $actual = is_object($resolved) ? $resolved::class : gettype($resolved);
            throw new InvalidContextTypeException($name, $type, $actual);
        }

        return $resolved;
    }

    private function ensureRequirementsWereMet(): void
    {
        foreach ($this->definition->eachContext() as $name) {

            $bound = $this->definition->findBoundContext($name);

            if (
                $this->definition->isRequired($name, $bound) &&
                $this->resolved[$name] === null &&
                ($bound === null || $this->resolved[$bound] !== null)
            ) {
                throw new CouldNotResolveRequiredContextException($name, $bound);
            }
        }
    }

    private function verifyContextIntegrity(): void
    {
        $this->resolver->verifyContextIntegrity($this->definition, $this->resolved);
    }

    /**
     * @return array<string, string|int|null>
     */
    private function serializeResolved(): array
    {
        return $this->resolver->serialize($this->definition, $this->resolved);
    }

    private function getResolverMethodName(string $name): string
    {
        return Str::camel('resolve-'.$name);
    }
}
