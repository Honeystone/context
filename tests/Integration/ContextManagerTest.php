<?php

declare(strict_types=1);

use Honeystone\Context\Exceptions\ContextIntegrityException;
use Honeystone\Context\Tests\Fixture\AuthenticatedProjectResolver;
use Honeystone\Context\Tests\Fixture\AuthenticatedTeamResolver;
use Honeystone\Context\Tests\Fixture\GuestResolver;
use Honeystone\Context\Tests\Fixture\InitalizationProbeResolver;
use Honeystone\Context\Tests\Fixture\InvalidUserResolver;
use Honeystone\Context\Tests\Fixture\Project;
use Honeystone\Context\Tests\Fixture\Team;
use Honeystone\Context\Tests\Fixture\TeamReceiver;
use Honeystone\Context\Tests\Fixture\User;
use Honeystone\Context\Tests\Fixture\UserResolver;
use Honeystone\Context\Tests\Fixture\ValidUserResolver;

use function Honeystone\Context\context;

it('manages a context', function (): void {

    context()->define()
        ->require('user', User::class)
        ->require('team', Team::class)
        ->accept('project', Project::class);

    context()->initialize(new AuthenticatedTeamResolver());

    expect(context()->initialized())->toBeTrue()
        ->and(context()->getInitializationKey())->toBeString()
        ->and(context('user'))->toBeInstanceOf(User::class)
        ->and(context('team'))->toBeInstanceOf(Team::class)
        ->and(context('project', false))->toBeNull();
});

it('validates an invalid context', function (): void {

    context()->define()
        ->require('user', User::class);

    context()->initialize(new InvalidUserResolver());
})
    ->throws(ContextIntegrityException::class);

it('validates an valid context', function (): void {

    context()->define()
        ->require('user', User::class);

    context()->initialize(new ValidUserResolver());

    expect(context('user'))->toBeInstanceOf(User::class);
});

it('reinitializes the context', function (): void {

    context()->define()
        ->accept('user', User::class)
        ->acceptWhenProvided('user', 'team', Team::class)
        ->acceptWhenProvided('team', 'project', Project::class);

    context()->initialize(new GuestResolver());

    $key = context()->getInitializationKey();

    expect(context()->initialized())->toBeTrue()
        ->and($key)->toBe(context()->getInitializationKey())
        ->and(context()->has('user'))->toBeFalse();

    context()->reinitialize(new AuthenticatedProjectResolver());

    expect(context()->initialized())->toBeTrue()
        ->and($key)->not()->toBe(context()->getInitializationKey())
        ->and(context('user'))->toBeInstanceOf(User::class)
        ->and(context('team'))->toBeInstanceOf(Team::class)
        ->and(context('project'))->toBeInstanceOf(Project::class);
});

test('context is not initialized during reinitialization', function (): void {

    context()->define() ->accept('user', User::class);

    context()->initialize(new GuestResolver());

    context()->reinitialize(new InitalizationProbeResolver());
})
    ->throws(UnexpectedValueException::class);

it('extends a context', function (): void {

    context()->define()->accept('user', User::class);

    context()->initialize(new UserResolver());

    $key = context()->getInitializationKey();

    context()->define()
        ->require('team', Team::class)
        ->acceptWhenProvided('team', 'project', Project::class);

    context()->extend(new AuthenticatedProjectResolver());

    expect(context()->initialized())->toBeTrue()
        ->and($key)->toBe(context()->getInitializationKey())
        ->and(context('user'))->toBeInstanceOf(User::class)
        ->and(context('team'))->toBeInstanceOf(Team::class)
        ->and(context('project'))->toBeInstanceOf(Project::class);
});

it('temporarily switches a context', function (): void {

    context()->define()
        ->accept('user', User::class)
        ->acceptWhenProvided('user', 'team', Team::class)
        ->acceptWhenProvided('team', 'project', Project::class);

    context()->initialize(new GuestResolver());

    $key = context()->getInitializationKey();

    $tmpContext = context()->temporarilyInitialize(new AuthenticatedProjectResolver());

    $tmpContext->start();

    expect(context()->initialized())->toBeTrue()
        ->and($key)->not()->toBe(context()->getInitializationKey())
        ->and(context('user'))->toBeInstanceOf(User::class)
        ->and(context('team'))->toBeInstanceOf(Team::class)
        ->and(context('project'))->toBeInstanceOf(Project::class);

    $tmpContext->end();

    expect(context()->initialized())->toBeTrue()
        ->and($key)->toBe(context()->getInitializationKey())
        ->and(context()->has('user'))->toBeFalse()
        ->and(context()->has('team'))->toBeFalse()
        ->and(context()->has('project'))->toBeFalse();
});

it('temporarily switches a context with a closure', function (): void {

    context()->define()
        ->accept('user', User::class)
        ->acceptWhenProvided('user', 'team', Team::class)
        ->acceptWhenProvided('team', 'project', Project::class);

    context()->initialize(new GuestResolver());

    $key = context()->getInitializationKey();

    context()->temporarilyInitialize(new AuthenticatedProjectResolver())
        ->run(function () use ($key): void {
            expect(context()->initialized())->toBeTrue()
                ->and($key)->not()->toBe(context()->getInitializationKey())
                ->and(context('user'))->toBeInstanceOf(User::class)
                ->and(context('team'))->toBeInstanceOf(Team::class)
                ->and(context('project'))->toBeInstanceOf(Project::class);
        });

    expect(context()->initialized())->toBeTrue()
        ->and($key)->toBe(context()->getInitializationKey())
        ->and(context()->has('user'))->toBeFalse()
        ->and(context()->has('team'))->toBeFalse()
        ->and(context()->has('project'))->toBeFalse();
});

it('notifies a receiver immediately', function (): void {

    context()->define()
        ->require('user', User::class)
        ->require('team', Team::class);

    context()->initialize(new AuthenticatedTeamResolver());

    $receiver = new TeamReceiver();

    context()->receive('team', $receiver);

    expect($receiver->getTeam())->toBeInstanceOf(Team::class);
});

it('notifies a receiver after initialization', function (): void {

    context()->define()
        ->require('user', User::class)
        ->require('team', Team::class);

    $receiver = new TeamReceiver();

    context()->receive('team', $receiver);

    context()->initialize(new AuthenticatedTeamResolver());

    expect($receiver->getTeam())->toBeInstanceOf(Team::class);
});

it('serializes and deserializes the context', function (): void {

    context()->define()
        ->require('user', User::class)
        ->require('team', Team::class);

    context()->initialize(new AuthenticatedTeamResolver());

    $key = context()->getInitializationKey();

    $serialized = context()->serialize();

    context()->deinitialize();

    expect(context()->initialized())->toBeFalse();

    context()->deserialize($serialized);

    expect(context()->initialized())->toBeTrue()
        ->and(context()->getInitializationKey())->toBe($key);
});
