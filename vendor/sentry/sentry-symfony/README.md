# sentry-symfony

Symfony integration for [Sentry](https://getsentry.com/).

[![Build Status](https://travis-ci.org/getsentry/sentry-symfony.svg?branch=master)](https://travis-ci.org/getsentry/sentry-symfony)

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

Add your DSN to ``app/config/config.yml``:

```yaml

sentry:
    dsn: "https://public:secret@sentry.example.com/1"
```


## Configuration

The following can be configured via ``app/config/config.yml``:

### app_path

The base path to your application. Used to trim prefixes and mark frames as part of your application.

```yaml
sentry:
    app_path: "/path/to/myapp"
```

### dsn

```yaml
sentry:
    dsn: "https://public:secret@sentry.example.com/1"
```

### environment

The environment your code is running in (e.g. production).

```yaml
sentry:
    environment: "%kernel.environment%"
```

### release

The version of your application. Often this is the git sha.

```yaml
sentry:
    release: "beeee2a06521a60e646bbb8fe38702e61e4929bf"
```

### prefixes

A list of prefixes to strip from filenames. Often these would be vendor/include paths.

```yaml
sentry:
    prefixes:
        - /usr/lib/include
```

### skip some exceptions

```yaml
sentry:
    skip_capture:
        - "Symfony\\Component\\HttpKernel\\Exception\\HttpExceptionInterface"
```

### error types

Define which error types should be reported.

```yaml
sentry:
    error_types: E_ALL & ~E_DEPRECATED & ~E_NOTICE
```
