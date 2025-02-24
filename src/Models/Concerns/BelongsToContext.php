<?php

declare(strict_types=1);

namespace Honeystone\Context\Models\Concerns;

use Honeystone\Context\Contracts\ReceivesContext;
use Honeystone\Context\Models\Scopes\ContextScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function Honeystone\Context\context;
use function method_exists;
use function property_exists;

/**
 * @mixin Model
 */
trait BelongsToContext
{
    public static function bootScopedByContext(): void
    {
        foreach (Arr::wrap(static::$context ?? []) as $name) {
            static::addGlobalScope(new ContextScope($name));
        }
    }

    public function initializeScopedByContext(): void
    {
        if (context()->initialized() && $this instanceof ReceivesContext) {

            foreach (Arr::wrap(static::$context ?? []) as $name) {
                context()->receive($name, $this);
            }
        }
    }

    public function receiveContext(string $name, ?object $context): void
    {
        if ($context !== null) {
            $this->addContextRelation($name, $context);
            $this->associateContext($name, $context);
        }
    }

    public function getContextForeignKey(string $name, object $context): string
    {
        $name = $this->aliasContext($name);

        $method = Str::camel("get-{$name}-ContextForeignKey");

        if (method_exists($this, $method)) {
            return $this->$method($context);
        }

        return $name.'_id';
    }

    public function getContextOwnerId(string $name, object $context): string|int
    {
        $name = $this->aliasContext($name);

        $method = Str::camel("get-{$name}-ContextOwnerId");

        if (method_exists($this, $method)) {
            return $this->$method($context);
        }

        return $context->id;
    }

    /**
     * Automatically add the context to its relationship if it's not already
     * been loaded.
     */
    private function addContextRelation(string $name, ?object $context): void
    {
        $name = $this->aliasContext($name);

        $method = Str::camel("get-{$name}-ContextRelationName");

        if (method_exists($this, $method)) {
            $name = $this->$method($context);
        }

        if (! $this->relationLoaded($name)) {
            $this->setRelation($name, $context);
        }
    }

    /**
     * Automatically associate context if this is a new model.
     */
    private function associateContext(string $name, object $context): void
    {
        if ($this->exists) {
            return;
        }

        $key = $this->getContextForeignKey($name, $context);

        if ($this->getAttribute($key) === null) {
            $this->setAttribute($key, $this->getContextOwnerId($name, $context));
        }
    }

    private function aliasContext(string $name): string
    {
        if (! property_exists($this, 'contextAliases')) {
            return $name;
        }

        return $this->contextAliases[$name] ?? $name;
    }
}
