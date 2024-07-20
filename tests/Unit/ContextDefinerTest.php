<?php

declare(strict_types=1);

use Honeystone\Context\ContextDefiner;
use Honeystone\Context\Tests\Fixture\Team;
use Honeystone\Context\Tests\Fixture\User;

it('defines required context', function (): void {

    $definer = new ContextDefiner();

    $definer->require('user', User::class);

    expect($definer->isAccepted('user'))->toBeTrue()
        ->and($definer->isRequired('user'))->toBeTrue()
        ->and($definer->findBoundContext('team'))->toBeNull()
        ->and($definer->getType('user'))->toBe(User::class);
});

it('defines required when context', function (): void {

    $definer = new ContextDefiner();

    $definer->accept('user', User::class);
    $definer->requireWhenProvided('user', 'team', Team::class);

    expect($definer->isAccepted('team'))->toBeTrue()
        ->and($definer->isRequired('team'))->toBeFalse()
        ->and($definer->findBoundContext('team'))->toBe('user')
        ->and($definer->getType('team'))->toBe(Team::class);
});

it('defines accepted context', function (): void {

    $definer = new ContextDefiner();

    $definer->accept('user', User::class);

    expect($definer->isAccepted('user'))->toBeTrue()
        ->and($definer->isRequired('user'))->toBeFalse()
        ->and($definer->findBoundContext('team'))->toBeNull()
        ->and($definer->getType('user'))->toBe(User::class);
});

it('defines accepted when context', function (): void {

    $definer = new ContextDefiner();

    $definer->accept('user', User::class);
    $definer->acceptWhenProvided('user', 'team', Team::class);

    expect($definer->isAccepted('team'))->toBeFalse()
        ->and($definer->isRequired('team'))->toBeFalse()
        ->and($definer->findBoundContext('team'))->toBe('user')
        ->and($definer->getType('team'))->toBe(Team::class);
});
