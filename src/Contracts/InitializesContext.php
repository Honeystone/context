<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

use Honeystone\Context\Exceptions\ContextAlreadyInitializedException;
use Honeystone\Context\Exceptions\ContextIntegrityException;
use Honeystone\Context\Exceptions\ContextNotInitializedException;
use Honeystone\Context\Exceptions\CouldNotResolveRequiredContextException;
use Honeystone\Context\Exceptions\InvalidContextTypeException;
use Honeystone\Context\Exceptions\NullResolvedContextException;
use Honeystone\Context\Exceptions\UndefinedContextException;

interface InitializesContext
{
    /**
     * Resolve the current context.
     *
     * @return $this
     *
     * @throws ContextAlreadyInitializedException
     * @throws InvalidContextTypeException
     * @throws CouldNotResolveRequiredContextException
     * @throws ContextIntegrityException
     */
    public function resolve(ResolvesContext $resolver): self;

    /**
     * Check if we have a resolved context. Strict mode will return false
     * for null contexts.
     *
     * @throws ContextNotInitializedException
     */
    public function hasResolved(string $name, bool $strict = true): bool;

    /**
     * Get a resolved context. Strict mode ensures an exception is thrown
     * when a context has resolved, but was null
     *
     * @throws ContextNotInitializedException
     * @throws UndefinedContextException
     * @throws NullResolvedContextException
     */
    public function getResolved(string $name, bool $strict = true): ?object;

    /**
     * Get all resolved contexts.
     *
     * @return array<string, object|null>
     *
     * @throws ContextNotInitializedException
     */
    public function getAllResolved(): array;

    /**
     * Get the definition used by this initializer.
     */
    public function getDefinition(): DefinesContext;

    /**
     * @return array{
     *     resolved: array<string, string|int|null>,
     *     resolver: class-string<ResolvesContext>,
     *     definition: array{
     *         contexts: array<string, class-string>,
     *         required_when: array<string, string|null>,
     *         accepted_when: array<string, string|null>,
     *     }
     * }
     *
     * @throws ContextNotInitializedException
     */
    public function serialize(): array;

    /**
     * @param array{
     *     resolved: array<string, string|int|null>,
     *     resolver: class-string<ResolvesContext>,
     *     definition: array{
     *         contexts: array<string, class-string>,
     *         required_when: array<string, string|null>,
     *         accepted_when: array<string, string|null>,
     *     }
     * } $data
     *
     * @return $this
     */
    public function deserialize(array $data): self;
}
