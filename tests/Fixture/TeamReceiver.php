<?php

declare(strict_types=1);

namespace Honeystone\Context\Tests\Fixture;

use Honeystone\Context\Contracts\ReceivesContext;

class TeamReceiver implements ReceivesContext
{
    private ?Team $team = null;

    public function receiveContext(string $name, ?object $context): void
    {
        $this->team = $context;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }
}
