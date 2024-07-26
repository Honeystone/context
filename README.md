# Application Context Manager for Laravel

![Static Badge](https://img.shields.io/badge/tests-passing-green)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/honeystone/context)](https://packagist.org/packages/honeystone/context)
![GitHub License](https://img.shields.io/github/license/honeystone/context)
![Packagist Dependency Version](https://img.shields.io/packagist/dependency-v/honeystone/context/php)
[![Static Badge](https://img.shields.io/badge/honeystone-fa6900)](https://honeystone.com/blog/weve-just-open-sourced-our-application-context-manager-for-laravel)


We developed `honeystone/context` for managing the application context in multi-tenant applications. It provides a
simple, fluent API for intializing, extending and switching contexts using 'context resolvers'. In addition, contexts
are automagically available in queued jobs and can be used to scope Eloquent models.

This package was developed several years ago for our own multi-tenant applications. We've recently decided to release it
in the hope it will be useful to the wider Laravel community. We're open to contributions, feedback and constructive
criticism.

## Getting started

Start by reading the
[short blog post](https://honeystone.com/blog/weve-just-open-sourced-our-application-context-manager-for-laravel)
that demonstrates how to use this package in multi-tenant application.

## Support us

[![Support Us](https://honeystone.com/images/github/support-us.webp)](https://honeystone.com)

We are committed to delivering high-quality open source packages maintained by the team at Honeystone. If you would
like to support our efforts, simply use our packages, recommend them and contribute.

If you need any help with your project, or require any custom development, please [get in touch](https://honeystone.com/contact-us).

## Installation

```shell
composer require honeystone/context
```

## Usage

A typical use-case would be to declare and initialize the context in your middleware.
You can then access the context data like this:

```php
$team = context('team');
$project = context('project');
```

### Defining the context

Contexts need to be defined before they can be resolved:

```php
context()->define()
    ->require('team', Team::class)
    ->require('project', Project::class);
```

These context definitions are 'required', so if they cannot be resolved a `CouldNotResolveRequiredContextException` will
be thrown. For undefined contexts, a `UndefinedContextException` will be thrown.

You can also define 'accepted', but not 'required' contexts:

```php
context()->define()
    ->accept('team', Team::class)
    ->accept('project', Project::class);
```

Or accept / require based on the existence of another context:

```php
context()->define()
    ->require('team', Team::class)
    ->requireWhenProvided('team', 'project', Project::class); //Or acceptWhenProvided
```

### Initializing the context

To initialize the context, you'll need to provide a resolver:

```php
context()->initialize(new MyResolver());
```

Here's an example resolver class:

```php
<?php

declare(strict_types=1);

namespace App\Context\Resolvers;

use Honeystone\Context\ContextResolver;

class MyResolver extends ContextResolver
{
    public function __construct(
        private ?Team = null,
        private ?Project = null,
    ) {}

    public function resolveTeam(): Team
    {
        //your resolution logic

        return $this->team;
    }

    public function resolveProject(): Project
    {
        //your resolution logic

        return $this->project;
    }

    public static function deserialize(array $data): static
    {
        //you must also declare a deserialization method,
        //to reinstate the serialised data

        return new static($team, $project);
    }
}
```

You can customize serialization logic like this:

```php
class MyResolver extends ContextResolver
{
    //...

    public function serializeTeam(Team $team): int
    {
        return $team->id;
    }
}
```

You can validate the integrity of a resolved context like this:

```php
class MyResolver extends ContextResolver
{
    //...

    public function checkProject(
        DefinesContext $definition,
        Project $project,
        array $resolved,
    ): bool {
        return $project->id === $resolved['team']->project_id;
    }
}
```

You can also reinitialize or deinitialize the current context:

```php
context()->reinitialize(new AnotherResolver());

context()->deinitialize();
```

### Extending the context

The current context can be extended using another resolver:

```php
context()->define()->accept('site', Site::class);

context()->extend(new AnotherResolver());
```

### Temporarily switching the context

Using a closure:

```php
context()->initialize(new MyResolver());

context()->temporarilyInitialize(new AnotherResolver())
    ->run(function () {
        //do something
    });
```

Using the start and end methods:

```php
context()->initialize(new MyResolver());

$tmpContext = context()->temporarilyInitialize(new AnotherResolver());

$tmpContext->start();

//do something

$tmpContext->end();
```

### Events and receivers

The `Honeystone\Context\Events\ContextChanged` event is dispatched whenever the context is changed.

You can also specify receivers to be notified when individual context values are set:

```php
context()->receive('team', new MyReceiver());
```

Here's an example receiver:

```php
<?php

declare(strict_types=1);

namespace App\Context\Receivers;

use Honeystone\Context\Contracts\ReceivesContext;

class TeamReceiver implements ReceivesContext
{
    public function receiveContext(string $name, ?object $context): void
    {
        //process received context
    }
}
```

### Scoping models using the context

You can use the current context to scope your Eloquent models.

Here's an example:

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Honeystone\Context\Models\Concerns\BelongsToContext;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BelongsToContext;

    protected static array $context = ['team'];

    //optionally specify context aliases
    protected static array $context = ['team' => 'my_team'];

    //optionally configure a context foreign key
    protected function getTeamContextForeignKey(Team $team): string
    {
        return 'my_team_id'; //the default is generated like this
    }

    //optionally configure a context owner id
    protected function getTeamContextOwnerId(Team $team): int
    {
        return $context->id; //defaults to the id prop
    }

    //optionally configure the relationship name
    protected function getTeamContextRelationName(Team $team): string
    {
        return 'my_team'; //the default is generated like this
    }
}
```

## Known issues

- Tests could do with improvement

## Laravel's "context"

When this solution was created, Laravel context did not exist. The function name collision is unfortunate, but not
really a problem. You'll just need to make sure you import the function.

```php
use function Honeystone\Context\context;
```

Whilst the name will invite comparison, these packages are solving different problems. Laravel context is a generic
global data store. This package is specifically for resolving context objects with more complex logic and using them to
scope the application, for example in a multi-tenancy application.

## Changelog

A list of changes can be found in the [CHANGELOG.md](CHANGELOG.md) file.

## License

[MIT](LICENSE.md) © [Honeystone Consulting Ltd](https://honeystone.com)
