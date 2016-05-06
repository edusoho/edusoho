#Flexihash
[![Build Status](https://travis-ci.org/dmnc/flexihash.svg?branch=master)](https://travis-ci.org/dmnc/flexihash) [![Coverage Status](https://coveralls.io/repos/dmnc/flexihash/badge.svg?branch=master&service=github)](https://coveralls.io/github/dmnc/flexihash?branch=master)

Flexihash is a small PHP library which implements [consistent hashing](http://en.wikipedia.org/wiki/Consistent_hashing), which is most useful in distributed caching.  It requires PHP5 and uses [SimpleTest](http://simpletest.org/) for unit testing.

This is a fork from PDA's [flexihash](https://github.com/pda/flexihash) created to add composer support and meet PSR standards.

##Usage Example

<pre>
&lt;?php

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
</pre>


##Roadmap
- [ ] v1 Initial packagist release
  - [x] Composer support
  - [ ] PSR2
- [ ] v2 API breaking refactor
  - [x] Migrate tests to PHPUnit
  - [ ] Introduce namespacing
  - [ ] PSR4 autoloading
  - [x] Automated testing

##Further Reading

  * http://www.spiteful.com/2008/03/17/programmers-toolbox-part-3-consistent-hashing/
  * http://weblogs.java.net/blog/tomwhite/archive/2007/11/consistent_hash.html
