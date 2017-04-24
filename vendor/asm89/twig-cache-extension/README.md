Twig cache extension
====================

The missing cache extension for Twig. The extension allows for caching rendered parts of
templates using several cache strategies.

[![Build Status](https://secure.travis-ci.org/asm89/twig-cache-extension.png?branch=master)](http://travis-ci.org/asm89/twig-cache-extension)

## Installation

The extension is installable via composer:

```json
{
    "require": {
        "asm89/twig-cache-extension": "~1.0"
    }
}
```

## Quick start

### Setup

A minimal setup for adding the extension with the `LifeTimeCacheStrategy` and
doctrine array cache is as following:

```php
<?php

use Doctrine\Common\Cache\ArrayCache;
use Asm89\Twig\CacheExtension\CacheProvider\DoctrineCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;

$cacheProvider  = new DoctrineCacheAdapter(new ArrayCache());
$cacheStrategy  = new LifetimeCacheStrategy($cacheProvider);
$cacheExtension = new CacheExtension($cacheStrategy);

$twig->addExtension($cacheExtension);
```

### Want to use a PSR-6 cache pool?

Instead of using the default `DoctrineCacheAdapter` the extension also has 
a `PSR-6` compatible adapter. You need to instantiate one of the cache pool
implementations as can be found on: http://php-cache.readthedocs.io/en/latest/

Example: Making use of the `ApcuCachePool` via the `PsrCacheAdapter`:

```bash
composer require cache/apcu-adapter
```

```php
<?php

use Asm89\Twig\CacheExtension\CacheProvider\PsrCacheAdapter;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;
use Cache\Adapter\Apcu\ApcuCachePool;

$cacheProvider  = new PsrCacheAdapter(new ApcuCachePool());
$cacheStrategy  = new LifetimeCacheStrategy($cacheProvider);
$cacheExtension = new CacheExtension($cacheStrategy);

$twig->addExtension($cacheExtension);
```

### Usage

To cache a part of a template in Twig surround the code with a `cache` block.
The cache block takes two parameters, first an "annotation" part, second the
"value" the cache strategy can work with. Example:

```jinja
{% cache 'v1/summary' 900 %}
    {# heavy lifting template stuff here, include/render other partials etc #}
{% endcache %}
```

Cache blocks can be nested:

```jinja
{% cache 'v1' 900 %}
    {% for item in items %}
        {% cache 'v1' item %}
            {# ... #}
        {% endcache %}
    {% endfor %}
{% endcache %}
```

The annotation can also be an expression:

```jinja
{% set version = 42 %}
{% cache 'hello_v' ~ version 900 %}
    Hello {{ name }}!
{% endcache %}
```

## Cache strategies

The extension ships with a few cache strategies out of the box. Setup and usage
of all of them is described below.

### Lifetime

See the ["Quick start"](#quick-start) for usage information of the `LifetimeCacheStrategy`.

### Generational

Strategy for generational caching.

In theory the strategy only saves fragments to the cache with infinite
lifetime. The key of the strategy lies in the fact that the keys for blocks
will change as the value for which the key is generated changes.

For example: entities containing a last update time, would include a timestamp
in the key. For an interesting blog post about this type of caching see:
http://37signals.com/svn/posts/3113-how-key-based-cache-expiration-works

### Blackhole

Strategy for development mode.

In development mode it often not very useful to cache fragments. The blackhole
strategy provides an easy way to not cache anything it all. It always generates
a new key and does not fetch or save any fragments.

#### Setup

In order to use the strategy you need to setup a `KeyGenerator` class that is
able to generate a cache key for a given value.

The following naive example always assumes the value is an object with the methods
`getId()` and `getUpdatedAt()` method. The key then composed from the class
name, the id and the updated time of the object:

```php
<?php

use Asm89\Twig\CacheExtension\CacheStrategy\KeyGeneratorInterface;

class MyKeyGenerator implements KeyGeneratorInterface
{
    public function generateKey($value)
    {
        return get_class($value) . '_' . $value->getId() . '_' . $value->getUpdatedAt();
    }

}
```

Next the `GenerationalCacheStrategy` needs to be setup with the keygenerator.

```php
<?php

use Asm89\Twig\CacheExtension\CacheStrategy\GenerationalCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;

$keyGenerator   = new MyKeyGenerator();
$cacheProvider  = /* see Quick start */;
$cacheStrategy  = new GenerationalCacheStrategy($cacheProvider, $keyGenerator, 0 /* = infinite lifetime */);
$cacheExtension = new CacheExtension($cacheStrategy);

$twig->addExtension($cacheExtension);
```

#### Usage

The strategy expects an object as value for determining the cache key of the
block:

```jinja
{% cache 'v1/summary' item %}
    {# heavy lifting template stuff here, include/render other partials etc #}
{% endcache %}
```

### Using multiple strategies

Different cache strategies are useful for different usecases. It is possible to
mix multiple strategies in an application with the
`IndexedChainingCacheStrategy`. The strategy takes an array of `'name' =>
$strategy` and delegates the caching to the appropriate strategy.

#### Setup

```php
<?php

use Asm89\Twig\CacheExtension\CacheStrategy\IndexedChainingCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;

$cacheStrategy  = new IndexedChainingCacheStrategy(array(
    'time' => $lifetimeCacheStrategy,
    'gen'  => $generationalCacheStrategy,
));
$cacheExtension = new CacheExtension($cacheStrategy);

$twig->addExtension($cacheExtension);
```

#### Usage

The strategy expects an array with as key the name of the strategy which it
needs to delegate to and as value the appropriate value for the delegated
strategy.

```jinja
{# delegate to lifetime strategy #}
{% cache 'v1/summary' {time: 300} %}
    {# heavy lifting template stuff here, include/render other partials etc #}
{% endcache %}

{# delegate to generational strategy #}
{% cache 'v1/summary' {gen: item} %}
    {# heavy lifting template stuff here, include/render other partials etc #}
{% endcache %}
```

## Implementing a cache strategy

Creating separate caches for different access levels, languages or other
usecases can be done by implementing a custom cache strategy. In order to do so
implement the `CacheProviderInterface`. It is recommended to use composition
and wrap a custom strategy around an existing one.

## Authors

Alexander <iam.asm89@gmail.com>

## License

twig-cache-extension is licensed under the MIT License - see the LICENSE file for details
