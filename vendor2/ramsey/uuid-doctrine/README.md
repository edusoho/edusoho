# ramsey/uuid-doctrine

[![Gitter Chat][badge-gitter]][gitter]
[![Source Code][badge-source]][source]
[![Latest Version][badge-release]][release]
[![Software License][badge-license]][license]
[![Build Status][badge-build]][build]
[![HHVM Status][badge-hhvm]][hhvm]
[![Scrutinizer][badge-quality]][quality]
[![Coverage Status][badge-coverage]][coverage]
[![Total Downloads][badge-downloads]][downloads]

The ramsey/uuid-doctrine package provides the ability to use
[ramsey/uuid][ramsey-uuid] as a [Doctrine field type][doctrine-field-type].

This project adheres to a [Contributor Code of Conduct][conduct]. By participating in this project and its community, you are expected to uphold this code.

## Installation

The preferred method of installation is via [Packagist][] and [Composer][]. Run
the following command to install the package and add it as a requirement to
your project's `composer.json`:

```bash
composer require ramsey/uuid-doctrine
```

## Examples

To configure Doctrine to use ramsey/uuid as a field type, you'll need to set up
the following in your bootstrap:

``` php
\Doctrine\DBAL\Types\Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');
$entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('uuid', 'uuid');
```

Then, in your models, you may annotate properties by setting the `@Column`
type to `uuid`. Depending on your database engine, you may not be able to
auto-generate a UUID when inserting into the database, but this isn't a problem;
in your model's constructor (or elsewhere, depending on how you create instances
of your model), generate a `Ramsey\Uuid\Uuid` object for the property. Doctrine
will handle the rest.

For example, here we annotate an `@Id` column with the `uuid` type, and in the
constructor, we generate a version 4 UUID to store for this entity.

``` php
/**
 * @Entity
 * @Table(name="products")
 */
class Product
{
    /**
     * @var \Ramsey\Uuid\Uuid
     *
     * @Id
     * @Column(type="uuid")
     * @GeneratedValue(strategy="NONE")
     */
    protected $id;

    public function __construct()
    {
        $this->id = \Ramsey\Uuid\Uuid::uuid4();
    }

    public function getId()
    {
        return $this->id;
    }
}
```

### Binary Database Columns

In the previous example, Doctrine will create a database column of type `CHAR(36)`,
but you may also use this library to store UUIDs as binary strings. The
`UuidBinaryType` helps accomplish this.

In your bootstrap, place the following:

``` php
\Doctrine\DBAL\Types\Type::addType('uuid_binary', 'Ramsey\Uuid\Doctrine\UuidBinaryType');
$entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('uuid_binary', 'binary');
```

Then, when annotating model class properties, use `uuid_binary` instead of `uuid`:

    @Column(type="uuid_binary")

### More Information

For more information on getting started with Doctrine, check out the "[Getting
Started with Doctrine][doctrine-getting-started]" tutorial.

## Contributing

Contributions are welcome! Please read [CONTRIBUTING][] for details.

## Copyright and License

The ramsey/uuid-doctrine library is copyright © [Ben Ramsey](https://benramsey.com/) and
licensed for use under the MIT License (MIT). Please see [LICENSE][] for more
information.


[ramsey-uuid]: https://github.com/ramsey/uuid
[conduct]: https://github.com/ramsey/uuid-doctrine/blob/master/CONDUCT.md
[doctrine-field-type]: http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html
[packagist]: https://packagist.org/packages/ramsey/uuid-doctrine
[composer]: http://getcomposer.org/
[contributing]: https://github.com/ramsey/uuid-doctrine/blob/master/CONTRIBUTING.md
[doctrine-getting-started]: http://doctrine-orm.readthedocs.org/en/latest/tutorials/getting-started.html

[badge-gitter]: https://img.shields.io/badge/gitter-join_chat-brightgreen.svg?style=flat-square
[badge-source]: http://img.shields.io/badge/source-ramsey/uuid--doctrine-blue.svg?style=flat-square
[badge-release]: https://img.shields.io/packagist/v/ramsey/uuid-doctrine.svg?style=flat-square
[badge-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[badge-build]: https://img.shields.io/travis/ramsey/uuid-doctrine/master.svg?style=flat-square
[badge-hhvm]: https://img.shields.io/hhvm/ramsey/uuid-doctrine.svg?style=flat-square
[badge-quality]: https://img.shields.io/scrutinizer/g/ramsey/uuid-doctrine/master.svg?style=flat-square
[badge-coverage]: https://img.shields.io/coveralls/ramsey/uuid-doctrine/master.svg?style=flat-square
[badge-downloads]: https://img.shields.io/packagist/dt/ramsey/uuid-doctrine.svg?style=flat-square

[gitter]: https://gitter.im/ramsey/uuid
[source]: https://github.com/ramsey/uuid-doctrine
[release]: https://packagist.org/packages/ramsey/uuid-doctrine
[license]: https://github.com/ramsey/uuid-doctrine/blob/master/LICENSE
[build]: https://travis-ci.org/ramsey/uuid-doctrine
[hhvm]: http://hhvm.h4cc.de/package/ramsey/uuid-doctrine
[quality]: https://scrutinizer-ci.com/g/ramsey/uuid-doctrine/
[coverage]: https://coveralls.io/r/ramsey/uuid-doctrine?branch=master
[downloads]: https://packagist.org/packages/ramsey/uuid-doctrine
