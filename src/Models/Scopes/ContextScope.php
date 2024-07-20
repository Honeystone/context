<?php

declare(strict_types=1);

namespace Honeystone\Context\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

use function Honeystone\Context\context;

final class ContextScope implements Scope
{
    public function __construct(private readonly string $name)
    {
    }

    /**
     * @param Builder<Model> $builder
     * @param Model $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (context()->initialized() && context()->has($this->name)) {

            $builder->where(
                /** @phpstan-ignore-next-line */
                $model->qualifyColumn($model->getContextForeignKey($this->name, context($this->name))),
                /** @phpstan-ignore-next-line */
                $model->getContextOwnerId($this->name, context($this->name))
            );
        }
    }
}
