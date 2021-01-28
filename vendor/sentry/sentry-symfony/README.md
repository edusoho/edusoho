# sentry-symfony

Symfony integration for [Sentry](https://getsentry.com/).

[![Stable release][Last stable image]][Packagist link]
[![Unstable release][Last unstable image]][Packagist link]

[![Build status][Master build image]][Master build link]
[![Scrutinizer][Master scrutinizer image]][Master scrutinizer link]
[![Coverage Status][Master coverage image]][Master scrutinizer link]


## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require sentry/sentry-symfony
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Sentry\SentryBundle\SentryBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Configure the SDK

Add your [Sentry DSN](https://docs.sentry.io/quickstart/#configure-the-dsn) value of your project to ``app/config/config.yml``.
Leaving this value empty will effectively disable Sentry reporting.

```yaml
sentry:
    dsn: "https://public:secret@sentry.example.com/1"
```

## Configuration

The following options can be configured via ``app/config/config.yml``.

### Skip some exceptions

```yaml
sentry:
    skip_capture:
        - "Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface"
```

### Listeners' priority

You can change the priority of the 3 default listeners of this bundle with the `listener_priorities` key of your config.
The default value is `0`, and here are the 3 possible sub-keys:

```yaml
listener_priorities:
    request: 0
    kernel_exception: 0
    console_exception: 0
```

... respectively for the `onKernelRequest`, `onKernelException` and `onConsoleException` events.

### Options

In the following section you will find some of the available options you can configure, listed alphabetically. All available options and a more detailed description of each can be found [here](https://docs.sentry.io/clients/php/config/), in the Sentry documentation.

#### app_path

The base path to your application. Used to trim prefixes and mark frames of the stack trace as part of your application.

```yaml
sentry:
    options:
        app_path: "/path/to/myapp"
```

#### environment

The environment your code is running in (e.g. production).

```yaml
sentry:
    options:
        environment: "%kernel.environment%"
```

#### error types

Define which error types should be reported.

```yaml
sentry:
    options:
        error_types: E_ALL & ~E_DEPRECATED & ~E_NOTICE
```

#### exception_listener

This is used to replace the default exception listener that this bundle uses. The value must be a FQCN of a class implementing the SentryExceptionListenerInterface interface. See [Create a Custom ExceptionListener](#create-a-custom-exceptionlistener) for more details.

```yaml
sentry:
    options:
        exception_listener: AppBundle\EventListener\MySentryExceptionListener
```

#### prefixes

A list of prefixes to strip from filenames. Often these would be vendor/include paths.

```yaml
sentry:
    options:
        prefixes:
            - /usr/lib/include
```

#### release

The version of your application. Often this is the Git SHA hash of the commit.

```yaml
sentry:
    options:
        release: "beeee2a06521a60e646bbb8fe38702e61e4929bf"
```

#### tags

Define tags for the logged errors.

```yaml
sentry:
    options:
        tags:
            tag1: tagvalue
            tag2: tagvalue
```

### Deprecated configuration options

In previous releases of this bundle, up to 0.8.2, some of the previous options where set outside of the options level of the configuration file. Those still work but are deprecated, and they will be dropped in the stable 1.x release, so **you are advised to abandon them**; to provide forward compatibility, they can still be used alongside the standard syntax, but values must match. This is a list of those options:

```yaml
sentry:
    app_path: ~
    environment: ~
    error_types: ~
    excluded_app_paths: ~
    prefixes: ~
    release: ~
```

## Customization

It is possible to customize the configuration of the user context, as well as modify the client immediately before an exception is captured by wiring up an event subscriber to the events that are emitted by the default configured `ExceptionListener` (alternatively, you can also just define your own custom exception listener).

### Create a custom ExceptionListener

You can always replace the default `ExceptionListener` with your own custom listener. To do this, assign a different class to the `exception_listener` property in your Sentry configuration, e.g.:

```yaml
sentry:
    options:
        exception_listener: AppBundle\EventListener\MySentryExceptionListener
```

... and then define the custom `ExceptionListener` that implements the `SentryExceptionListenerInterface`, e.g.:

```php
// src/AppBundle/EventSubscriber/MySentryEventListener.php
namespace AppBundle\EventSubscriber;

use Sentry\SentryBundle\EventListener\SentryExceptionListenerInterface;
use Symfony\Component\Console\Event\ConsoleExceptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MySentryExceptionListener implements SentryExceptionListenerInterface
{
    // ...

    public function __construct(TokenStorageInterface $tokenStorage = null, AuthorizationCheckerInterface $authorizationChecker = null, \Raven_Client $client = null, array $skipCapture, EventDispatcherInterface $dispatcher = null)
    {
        // ...
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        // ...
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // ...
    }

    public function onConsoleException(ConsoleExceptionEvent $event)
    {
        // ...
    }
}
```

As a side note, while the above demonstrates a custom exception listener that
does not extend anything you could choose to extend the default
`ExceptionListener` and only override the functionality that you want to.

### Add an EventSubscriber for Sentry Events

Create a new class, e.g. `MySentryEventSubscriber`:

```php
// src/AppBundle/EventSubscriber/MySentryEventListener.php
namespace AppBundle\EventSubscriber;

use Sentry\SentryBundle\Event\SentryUserContextEvent;
use Sentry\SentryBundle\SentrySymfonyEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MySentryEventSubscriber implements EventSubscriberInterface
{
    /** @var \Raven_Client */
    protected $client;

    public function __construct(\Raven_Client $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return array(
            SentrySymfonyEvents::PRE_CAPTURE => 'onPreCapture',
            SentrySymfonyEvents::SET_USER_CONTEXT => 'onSetUserContext'
        );
    }

    public function onSetUserContext(SentryUserContextEvent $event)
    {
        // ...
    }

    public function onPreCapture(Event $event)
    {
        if ($event instanceof GetResponseForExceptionEvent) {
            // ...
        }
        elseif ($event instanceof ConsoleExceptionEvent) {
            // ...
        }
    }
}
```

In the example above, if you subscribe to the `PRE_CAPTURE` event you may
get an event object that caters more toward a response to a web request (e.g.
`GetResponseForExceptionEvent`) or one for actions taken at the command line
(e.g. `ConsoleExceptionEvent`). Depending on what and how the code was
invoked, and whether or not you need to distinguish between these events
during pre-capture, it might be best to test for the type of the event (as is
demonstrated above) before you do any relevant processing of the object.

To configure the above add the following configuration to your services
definitions:

```yaml
app.my_sentry_event_subscriber:
    class: AppBundle\EventSubscriber\MySentryEventSubscriber
    arguments:
      - '@sentry.client'
    tags:
      - { name: kernel.event_subscriber }
```

[Last stable image]: https://poser.pugx.org/sentry/sentry-symfony/version.svg
[Last unstable image]: https://poser.pugx.org/sentry/sentry-symfony/v/unstable.svg
[Master build image]: https://travis-ci.org/getsentry/sentry-symfony.svg?branch=master
[Master scrutinizer image]: https://scrutinizer-ci.com/g/getsentry/sentry-symfony/badges/quality-score.png?b=master
[Master coverage image]: https://scrutinizer-ci.com/g/getsentry/sentry-symfony/badges/coverage.png?b=master

[Packagist link]: https://packagist.org/packages/sentry/sentry-symfony
[Master build link]: https://travis-ci.org/getsentry/sentry-symfony
[Master scrutinizer link]: https://scrutinizer-ci.com/g/getsentry/sentry-symfony/?branch=master
