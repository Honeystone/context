<?php

declare(strict_types=1);

namespace Honeystone\Context\Contracts;

interface ReceivesContext
{
    public function receiveContext(string $name, ?object $context): void;
}
