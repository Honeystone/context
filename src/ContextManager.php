<?php

declare(strict_types=1);

namespace Honeystone\Context;

use Honeystone\Context\Contracts\DefinesContext;
use Honeystone\Context\Contracts\InitializesContext;
use Honeystone\Context\Contracts\InitializesTemporaryContext;
use Honeystone\Context\Contracts\ManagesContext;
use Honeystone\Context\Contracts\ProvidesCurrentUser;
use Honeystone\Context\Contracts\ReceivesContext;
use Honeystone\Context\Contracts\ResolvesContext;
use Honeystone\Context\Events\ContextChanged;
use Honeystone\Context\Exceptions\ContextAlreadyInitializedException;
use Honeystone\Context\Exceptions\ContextNotInitializedException;
use Illuminate\Support\Str;

use function event;

final class ContextManager implements ManagesContext
{
    private ?string $key = null;

    private ?DefinesContext $definition = null;

    private ?InitializesContext $initializer = null;

    private bool $initialized = false;

    /**
     * @var array<InitializesContext>
     */
    private array $extensions = [];

    /**
     * @var array<string, array<ReceivesContext>>
     */
    private array $receivers = [];

    public function __construct(private readonly ProvidesCurrentUser $userProvider)
    {
    }

    public function initialized(): bool
    {
        return $this->initialized;
    }

    public function getInitializationKey(): ?string
    {
        return $this->key;
    }

    public function auth(): ProvidesCurrentUser
    {
        return $this->userProvider;
    }

    public function define(): DefinesContext
    {
        return $this->definition = new ContextDefiner();
    }

    public function initialize(ResolvesContext $resolver): self
    {
        if ($this->initialized()) {
            throw new ContextAlreadyInitializedException();
        }

        $resolver->define($this->getCurrentDefinition());

        $this->generateNewKey();
        $this->resetExtensions();

        $this->initializer = new ContextInitializer($this->getCurrentDefinition(), $this);
        $this->initializer->resolve($resolver);
        $this->initialized = true;

        $this->define();
        $this->notifyReceivers();

        event(new ContextChanged($this, true));

        return $this;
    }

    public function reinitialize(ResolvesContext $resolver): self
    {
        $first = !$this->initialized();

        $definition = $this->getCurrentDefinition();

        if ($this->initialized() && !$definition->hasDefinitions()) {
            $definition = $this->initializer->getDefinition();
        }

        $resolver->define($definition);

        $this->generateNewKey();
        $this->resetExtensions();

        $this->initializer = new ContextInitializer($definition, $this);
        $this->initializer->resolve($resolver);
        $this->initialized = true;

        $this->define();
        $this->notifyReceivers();

        event(new ContextChanged($this, $first));

        return $this;
    }

    public function temporarilyInitialize(ResolvesContext $resolver): InitializesTemporaryContext
    {
        $definition = $this->getCurrentDefinition();

        if ($this->initialized() && !$definition->hasDefinitions()) {
            $definition = $this->initializer->getDefinition();
        }

        $resolver->define($definition);

        $oldKey = $this->key;
        $oldInitializer = $this->initializer;
        $oldExtensions = $this->extensions;

        $tmpInitializer = (new TemporaryContextInitializer(
            fn (
                InitializesTemporaryContext $initializer
            ): ManagesContext => $this->startTemporaryInitialization($initializer),
            fn (): ManagesContext => $this->endTemporaryInitialization($oldKey, $oldInitializer, $oldExtensions),
            new ContextInitializer($definition, $this),
        ));

        $tmpInitializer->resolve($resolver);

        return $tmpInitializer;
    }

    public function deinitialize(): self
    {
        $this->key = null;
        $this->resetExtensions();

        $this->initializer = null;
        $this->initialized = false;

        $this->define();

        event(new ContextChanged($this));

        return $this;
    }

    public function extend(ResolvesContext $resolver): self
    {
        if (!$this->initialized()) {
            throw new ContextNotInitializedException();
        }

        $resolver->define($this->getCurrentDefinition());

        $this->extensions[] = (new ContextInitializer($this->getCurrentDefinition(), $this))->resolve($resolver);

        $this->define();
        $this->notifyReceivers();

        event(new ContextChanged($this));

        return $this;
    }

    public function has(string $name, bool $strict = true): bool
    {
        if (!$this->initialized()) {
            throw new ContextNotInitializedException();
        }

        if ($this->initializer->hasResolved($name, $strict)) {
            return true;
        }

        foreach ($this->extensions as $extension) {
            if ($extension->hasResolved($name, $strict)) {
                return true;
            }
        }

        return false;
    }

    public function get(string $name, bool $strict = true): ?object
    {
        if (!$this->initialized()) {
            throw new ContextNotInitializedException();
        }

        if ($this->initializer->hasResolved($name, false)) {
            return $this->initializer->getResolved($name, $strict);
        }

        foreach ($this->extensions as $extension) {
            if ($extension->hasResolved($name, false)) {
                return $extension->getResolved($name, $strict);
            }
        }

        return null;
    }

    public function receive(string $name, ReceivesContext $receiver): void
    {
        $this->receivers[$name][] = $receiver;

        if (!$this->initialized()) {
            return;
        }

        foreach ($this->getAllInitializers() as $initializer) {
            if ($initializer->hasResolved($name)) {
                $receiver->receiveContext($name, $initializer->getResolved($name));
                break;
            }
        }
    }

    public function serialize(): array
    {
        if (!$this->initialized()) {
            throw new ContextNotInitializedException();
        }

        $data = [
            'key' => $this->key,
            'user' => $this->auth()->serialize(),
            'initialized' => $this->initializer->serialize(),
            'initialized_extensions' => [],
        ];

        foreach ($this->extensions as $initializer) {
            $data['initialized_extensions'][] = $initializer->serialize();
        }

        return $data;
    }

    public function deserialize(array $data): self
    {
        $first = !$this->initialized();

        $this->key = $data['key'];
        $this->resetExtensions();

        $this->auth()->deserialize($data['user']);

        $this->initializer = (new ContextInitializer($this->define(), $this))->deserialize($data['initialized']);
        $this->initialized = true;

        foreach ($data['initialized_extensions'] as $initialized) {
            $this->extensions[] = (new ContextInitializer($this->define(), $this))->deserialize($initialized);
        }

        $this->define();
        $this->notifyReceivers();

        event(new ContextChanged($this, $first));

        return $this;
    }

    private function notifyReceivers(): void
    {
        foreach ($this->receivers as $name => $receivers) {

            $context = null;

            foreach ($this->getAllInitializers() as $initializer) {
                if ($initializer->hasResolved($name)) {
                    $context = $initializer->getResolved($name);
                    break;
                }
            }

            foreach ($receivers as $receiver) {
                $receiver->receiveContext($name, $context);
            }
        }
    }

    private function generateNewKey(): void
    {
        $this->key = (string) Str::uuid();
    }

    private function getCurrentDefinition(): DefinesContext
    {
        return $this->definition ?? $this->define();
    }

    private function resetExtensions(): void
    {
        $this->extensions = [];
    }

    /**
     * @return array<InitializesContext>
     */
    private function getAllInitializers(): array
    {
        return $this->initialized() ? [$this->initializer, ...$this->extensions] : [];
    }

    /**
     * @return $this
     */
    private function startTemporaryInitialization(InitializesTemporaryContext $initializer): self
    {
        $first = $this->initialized();

        $this->generateNewKey();
        $this->resetExtensions();

        $this->initializer = $initializer;
        $this->initialized = true;

        $this->define();
        $this->notifyReceivers();

        event(new ContextChanged($this, $first));

        return $this;
    }

    /**
     * @param array<InitializesContext> $oldExtensions
     *
     * @return $this
     */
    private function endTemporaryInitialization(
        ?string $oldKey,
        ?InitializesContext $oldInitializer,
        array $oldExtensions,
    ): self {

        $this->key = $oldKey;
        $this->extensions = $oldExtensions;

        $this->initializer = $oldInitializer;
        $this->initialized = $oldInitializer !== null;

        $this->define();
        $this->notifyReceivers();

        event(new ContextChanged($this));

        return $this;
    }
}
