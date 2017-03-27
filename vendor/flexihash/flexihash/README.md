#Flexihash
[![Build Status](https://travis-ci.org/pda/flexihash.svg?branch=master)](https://travis-ci.org/pda/flexihash) [![Coverage Status](https://coveralls.io/repos/github/pda/flexihash/badge.svg?branch=master)](https://coveralls.io/github/pda/flexihash?branch=master)

Flexihash is a small PHP library which implements [consistent hashing](http://en.wikipedia.org/wiki/Consistent_hashing), which is most useful in distributed caching. It requires PHP5 and uses [PHPUnit](http://simpletest.org/) for unit testing.

##Installation

[Composer](https://getcomposer.org/) is the recommended installation technique. You can find flexihash on [Packagist](https://packagist.org/packages/flexihash/flexihash) so installation is as easy as
```
composer require flexihash/flexihash
```
or in your `composer.json`
```json
{
    "require": {
        "flexihash/flexihash": "^2.0.0"
    }
}
```

##Usage

```php
$hash = new Flexihash();

// bulk add
$hash->addTargets(array('cache-1', 'cache-2', 'cache-3'));

// simple lookup
$hash->lookup('object-a'); // "cache-1"
$hash->lookup('object-b'); // "cache-2"

// add and remove
$hash
  ->addTarget('cache-4')
  ->removeTarget('cache-1');

// lookup with next-best fallback (for redundant writes)
$hash->lookupList('object', 2); // ["cache-2", "cache-4"]

// remove cache-2, expect object to hash to cache-4
$hash->removeTarget('cache-2');
$hash->lookup('object'); // "cache-4"
```

##Further Reading

  * http://www.spiteful.com/2008/03/17/programmers-toolbox-part-3-consistent-hashing/
  * http://weblogs.java.net/blog/tomwhite/archive/2007/11/consistent_hash.html
