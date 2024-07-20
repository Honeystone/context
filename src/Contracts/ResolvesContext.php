<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

use Honeystone\Context\Exceptions\ContextIntegrityException;

interface ResolvesContext
{
    /**
     * Define the context to be resolved.
     */
    public function define(DefinesContext $definition): void;

    /**
     * @param array<string, object|null> $resolved
     *
     * @throws ContextIntegrityException
     */
    public function verifyContextIntegrity(DefinesContext $definition, array $resolved = []): void;

    /**
     * @param array<string, object|null> $resolved
     *
     * @return array<string, string|int|null>
     */
    public function serialize(DefinesContext $definition, array $resolved = []): array;

    /**
     * @param array<string, string|int|null> $data
     *
     * @return static
     */
    public static function deserialize(array $data): self;
}
