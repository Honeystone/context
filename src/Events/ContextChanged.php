<?php

declare(strict_types=1);

namespace Honeystone\Context\Events;

use Honeystone\Context\Contracts\ManagesContext;

final class ContextChanged
{
    public function __construct(
        private readonly ManagesContext $context,
        private readonly bool $firstInitialization = false,
    )
    {
    }

    public function getContext(): ManagesContext
    {
        return $this->context;
    }

    /**
     * Check if this was the first initialization.
     */
    public function isFirstInitialization(): bool
    {
        return $this->firstInitialization;
    }
}
