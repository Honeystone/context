<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Honeystone\Context\Contracts\ManagesContext;

function context(?string $name = null, bool $strict = true): ?object
{
    /** @var ManagesContext $context */
    $context = app(ManagesContext::class);

    if ($name !== null) {
        return $context->get($name, $strict);
    }

    return $context;
}
