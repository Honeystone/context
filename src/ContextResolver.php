<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Honeystone\Context\Contracts\DefinesContext;
use Honeystone\Context\Contracts\ResolvesContext;
use Honeystone\Context\Exceptions\ContextIntegrityException;
use Illuminate\Support\Str;

use function method_exists;

abstract class ContextResolver implements ResolvesContext
{
    public function define(DefinesContext $definition): void
    {
        //no action
    }

    public function verifyContextIntegrity(DefinesContext $definition, array $resolved = []): void
    {
        foreach ($definition->eachContext() as $name) {

            $method = $this->getIntegrityCheckMethodName($name);

            if (
                method_exists($this, $method) &&
                !$this->$method($definition, $resolved[$name] ?? null, $resolved)
            ) {
                throw new ContextIntegrityException($name);
            }
        }
    }

    public function serialize(DefinesContext $definition, array $resolved = []): array
    {
        $data = [];

        foreach ($definition->eachContext() as $name) {
            $data[$name] = $this->getSerializedContext($name, $resolved[$name] ?? null);
        }

        return $data;
    }

    private function getSerializedContext(string $name, ?object $context): string|int|null
    {
        $method = $this->getSerializeMethodName($name);

        if (method_exists($this, $method)) {
            return $this->$method($context);
        }

        return $context?->id;
    }

    private function getIntegrityCheckMethodName(string $name): string
    {
        return Str::camel('check-'.$name);
    }

    private function getSerializeMethodName(string $name): string
    {
        return Str::camel('serialize-'.$name);
    }
}
