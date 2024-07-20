<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

use Honeystone\Context\Exceptions\ContextAlreadyInitializedException;
use Honeystone\Context\Exceptions\ContextNotInitializedException;
use Honeystone\Context\Exceptions\NullResolvedContextException;
use Honeystone\Context\Exceptions\UndefinedContextException;

interface ManagesContext
{
    /**
     * Check if the context has been initialized.
     *
     * @return bool
     */
    public function initialized(): bool;

    /**
     * After initialization a unique key is generated;
     * null when not initialized.
     *
     * @return string|null
     */
    public function getInitializationKey(): ?string;

    public function auth(): ProvidesCurrentUser;

    /**
     * Define the application context.
     *
     * @return DefinesContext
     *
     * @throws ContextAlreadyInitializedException
     */
    public function define(): DefinesContext;

    /**
     * Initializes the application context.
     *
     * @return $this
     *
     * @throws ContextAlreadyInitializedException
     */
    public function initialize(ResolvesContext $resolver): self;

    /**
     * Initializes the application context.
     *
     * @return $this
     */
    public function reinitialize(ResolvesContext $resolver): self;

    /**
     * Starts building a temporary context to run some code within.
     */
    public function temporarilyInitialize(ResolvesContext $resolver): InitializesTemporaryContext;

    /**
     * Deinitialize the application context.
     *
     * @return $this
     */
    public function deinitialize(): self;

    /**
     * Extend the current context without reinitialisation. Extensions are
     * independent of exiting contexts and have zero knowledge of them.
     *
     * @throws ContextNotInitializedException
     *
     * @return $this
     */
    public function extend(ResolvesContext $resolver): self;

    /**
     * Check if we have an initialized context. Strict mode will return false
     * for null contexts.
     *
     * @throws ContextNotInitializedException
     */
    public function has(string $name, bool $strict = true): bool;

    /**
     * Get an initialized context. Strict mode ensures an exception is thrown
     * when a context has resolved, but was null
     *
     * @throws ContextNotInitializedException
     * @throws UndefinedContextException
     * @throws NullResolvedContextException
     */
    public function get(string $name, bool $strict = true): ?object;

    /**
     * Receivers will be passed the resolved context on initialization, or
     * immediately if already initialized.
     *
     * @throws ContextNotInitializedException
     */
    public function receive(string $name, ReceivesContext $receiver): void;

    /**
     * @return array{
     *     key: string,
     *     user: int|null,
     *     initialized: array{
     *         resolved: array<string, string|int|null>,
     *         resolver: class-string<ResolvesContext>,
     *         definition: array{
     *             contexts: array<string, class-string>,
     *             required_when: array<string, string|null>,
     *             accepted_when: array<string, string|null>,
     *         }
     *     },
     *     initialized_extensions: array<
     *         array{
     *             resolved: array<string, string|int|null>,
     *             resolver: class-string<ResolvesContext>,
     *             definition: array{
     *                 contexts: array<string, class-string>,
     *                 required_when: array<string, string|null>,
     *                 accepted_when: array<string, string|null>,
     *             }
     *         },
     *     >,
     * }
     *
     * @throws ContextNotInitializedException
     */
    public function serialize(): array;

    /**
     * @param array{
     *     key: string,
     *     user: int|null,
     *     initialized: array{
     *         resolved: array<string, string|int|null>,
     *         resolver: class-string<ResolvesContext>,
     *         definition: array{
     *             contexts: array<string, class-string>,
     *             required_when: array<string, string|null>,
     *             accepted_when: array<string, string|null>,
     *         }
     *     },
     *     initialized_extensions: array<
     *         array{
     *             resolved: array<string, string|int|null>,
     *             resolver: class-string<ResolvesContext>,
     *             definition: array{
     *                 contexts: array<string, class-string>,
     *                 required_when: array<string, string|null>,
     *                 accepted_when: array<string, string|null>,
     *             }
     *         },
     *     >,
     * } $data
     *
     * @return $this
     */
    public function deserialize(array $data): self;
}
