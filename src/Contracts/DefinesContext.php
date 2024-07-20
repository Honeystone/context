<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

use Generator;

interface DefinesContext
{
    /**
     * Require context.
     *
     * @param class-string $type
     *
     * @return $this
     */
    public function require(string $name, string $type): self;

    /**
     * Require context when another has been provided.
     *
     * @param class-string $type
     *
     * @return $this
     */
    public function requireWhenProvided(string $provided, string $name, string $type): self;

    /**
     * Accept context.
     *
     * @param class-string $type
     *
     * @return $this
     */
    public function accept(string $name, string $type): self;

    /**
     * Accept context when another has been provided.
     *
     * @param class-string $type
     *
     * @return $this
     */
    public function acceptWhenProvided(string $provided, string $name, string $type): self;

    /**
     * Check if a context is required.
     */
    public function isRequired(string $name, ?string $bound = null): bool;

    /**
     * Check if a context is accepted.
     */
    public function isAccepted(string $name, ?string $bound = null): bool;

    /**
     * Check if a context is defined.
     */
    public function isDefined(string $name): bool;

    /**
     * Find a context the named is bound to.
     */
    public function findBoundContext(string $name): ?string;

    /**
     * Get the type for the given context.
     *
     * @return class-string
     */
    public function getType(string $name): string;

    /**
     * Loop through defined contexts.
     *
     * @return Generator<string>
     */
    public function eachContext(): Generator;

    /**
     * Check if any contexts have been defined.
     */
    public function hasDefinitions(): bool;

    /**
     * @return array{
     *     contexts: array<string, class-string>,
     *     required_when: array<string, string|null>,
     *     accepted_when: array<string, string|null>,
     * }
     */
    public function serialize(): array;

    /**
     * @param array{
     *     contexts: array<string, class-string>,
     *     required_when: array<string, string|null>,
     *     accepted_when: array<string, string|null>,
     * } $data
     *
     * @return $this
     */
    public function deserialize(array $data): self;
}
