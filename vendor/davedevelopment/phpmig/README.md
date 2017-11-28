Phpmig
======

[![Build
Status](https://travis-ci.org/davedevelopment/phpmig.png)](https://travis-ci.org/davedevelopment/phpmig)

What is it?
-----------

Phpmig is a (database) migration tool for php, that should be adaptable for use
with most PHP 5.3+ projects. It's kind of like [doctrine
migrations][doctrinemigrations], without the [doctrine][doctrine]. Although you
can use doctrine if you want. And ironically, I use doctrine in my examples.

How does it work?
-----------------

```bash
$ phpmig migrate
```

Phpmig aims to be vendor/framework independent, and in doing so, requires you to
do a little bit of work up front to use it.

Phpmig requires a bootstrap file, that must return an object that implements the
ArrayAccess interface with several predefined keys. We recommend returning an
instance of [Pimple][pimple], a simple dependency injection container. This is
also an ideal opportunity to expose your own services to the migrations
themselves, which have access to the container, such as a [schema management
abstraction][doctrineschemamanager].

Getting Started
---------------

The best way to install phpmig is using composer:

```bash
$ curl -sS https://getcomposer.org/installer | php
$ php composer.phar require davedevelopment/phpmig
```

You can then use the localised version of phpmig for that project

```bash
$ bin/phpmig --version
```

Phpmig can do a little configuring for you to get started, go to the root of
your project and:

```bash
$ phpmig init
+d ./migrations Place your migration files in here
+f ./phpmig.php Create services in here
$ 
```

Note that you can move phpmig.php to config/phpmig.php, the commands will look
first in the config directory than in the root.

Phpmig can generate migrations using the generate command. Migration files are named
versionnumber_name.php, where version number is made up of 0-9 and name is
CamelCase or snake\_case. Each migration file should contain a class with the
same name as the file in CamelCase.

```bash
$ phpmig generate AddRatingToLolCats
+f ./migrations/20111018171411_AddRatingToLolCats.php
$ phpmig status

 Status   Migration ID    Migration Name 
-----------------------------------------
   down  20111018171929  AddRatingToLolCats

Use the migrate command to run migrations

$ phpmig migrate
 == 20111018171411 AddRatingToLolCats migrating
 == 20111018171411 AddRatingToLolCats migrated 0.0005s
$ phpmig status

 Status   Migration ID    Migration Name 
-----------------------------------------
     up  20111018171929  AddRatingToLolCats
$ 
```

Better Persistence
------------------

The init command creates a bootstrap file that specifies a flat file to use to
track which migrations have been run, which isn't great. You can use the
provided adapters to store this information in your database.

```php
<?php

# phpmig.php

use Phpmig\Adapter;
use Pimple\Container;

$container = new Container();

$container['db'] = function () {
    $dbh = new PDO('mysql:dbname=testdb;host=127.0.0.1','username','passwd');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
};

$container['phpmig.adapter'] = function ($c) {
    return new Adapter\PDO\Sql($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;

```

### Postgres PDO `SqlPgsql` 
Adds support for qualifying the migrations table with a schema.

```php
<?php

# phpmig

use Phpmig\Adapter;
use Pimple\Container;

$container = new Container();

$container['db'] = function () {
    $dbh = new PDO(sprintf('pgsql:dbname=%s;host=%s;password=%s', 'dbname', 'localhost', 'password'), 'dbuser', '');
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
};

$container['phpmig.adapter'] = function ($c) {
    return new Adapter\PDO\SqlPgsql($c['db'], 'migrations', 'migrationschema');
};

return $container;
```



Or you can use Doctrine's DBAL:

```php
<?php

# phpmig.php

// do some autoloading of Doctrine here

use Phpmig\Adapter;
use Pimple\Container;
use Doctrine\DBAL\DriverManager;

$container = new Container();

$container['db'] = function () {
    return DriverManager::getConnection(array(
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . DIRECTORY_SEPARATOR . 'db.sqlite',
    ));
};

$container['phpmig.adapter'] = function ($c) {
    return new Adapter\Doctrine\DBAL($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
```

Setting up migrations with Zend Framework  requires a couple additional steps. You first need to prepare
the configuration. It might be in any format supported by Zend_Config. Here is an
example in YAML for MySQL:

```yaml
phpmig:
  tableName: migrations
  createStatement: CREATE TABLE migrations ( version VARCHAR(255) NOT NULL );
```

In configuration file you need to provide the table name where the migrations will
be stored and a create statement. You can use one of the configurations provided
in the config folder for some common RDBMS.

Here is how the bootstrap file should look like:

```php
<?php

# phpmig.php

// Set some constants
define('PHPMIG_PATH', realpath(dirname(__FILE__)));
define('VENDOR_PATH', PHPMIG_PATH . '/vendor');
set_include_path(get_include_path() . PATH_SEPARATOR . VENDOR_PATH);

// Register autoloading
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->registerNamespace('Zend_');

use Phpmig\Adapter\Zend\Db;
use Pimple\Container;

$container = new Container();

$container['db'] = function () {
    return Zend_Db::factory('pdo_mysql', array(
        'dbname' => 'DBNAME',
        'username' => 'USERNAME',
        'password' => 'PASSWORD',
        'host' => 'localhost'
    ));
};

$container['phpmig.adapter'] = function($c) {
    $configuration = null;
    $configurationFile = PHPMIG_PATH . '/config/mysql.yaml';

    if (file_exists($configurationFile)) {
        $configuration = new Zend_Config_Yaml($configurationFile, null, array('ignore_constants' => true));
    }

    return new Db($c['db'], $configuration);
};

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
```

Example with Eloquent ORM 5.1
------------------
```php
<?php

use Phpmig\Adapter;
use Pimple\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

$container = new Container();

$container['config'] = [
    'driver'    => 'xxx',
    'host'      => 'xxx',
    'database'  => 'xxx',
    'username'  => 'xxx',
    'password'  => 'x',
    'charset'   => 'xxx',
    'collation' => 'xxx',
    'prefix'    => '',
];

$container['db'] = function ($c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

   return $capsule;
};

$container['phpmig.adapter'] = function($c) {
    return new Adapter\Illuminate\Database($c['db'], 'migrations');
};
$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

return $container;
```


Writing Migrations
------------------

The migrations should extend the Phpmig\Migration\Migration class, and have
access to the container. For example, assuming you've rewritten your bootstrap
file like above:

```php
<?php

use Phpmig\Migration\Migration;

class AddRatingToLolCats extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $sql = "ALTER TABLE `lol_cats` ADD COLUMN `rating` INT(10) UNSIGNED NULL";
        $container = $this->getContainer(); 
        $container['db']->query($sql);
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $sql = "ALTER TABLE `lol_cats` DROP COLUMN `rating`";
        $container = $this->getContainer(); 
        $container['db']->query($sql);
    }
}
```

Customising the migration template
-----------------------------------

You can change the default migration template by providing the path to a file 
in the `phpmig.migrations_template_path` config value. If the template has a 
`.php` extension it is included and parsed as PHP, and the `$className` variable 
is replaced: 

```php
<?= "<?php ";?>

use Phpmig\Migration\Migration;

class <?= $className ?> extends Migration
{
    $someValue = <?= $this->container['value'] ?>; 

    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer();
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer();
    }
}
```

If it uses any other extension (e.g., `.stub` or `.tmpl`) it's parsed using the 
`sprintf` function, so the class name should be set to `%s` to ensure it gets 
replaced: 

```php
<?php

use Phpmig\Migration\Migration;

class %s extends Migration
{
    /**
     * Do the migration
     */
    public function up()
    {
        $container = $this->getContainer(); 
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $container = $this->getContainer(); 
    }
}
```

Module Migrations
---------------------

If you have an application that consists of different modules and you want to be able to separate the migration, Phpmig has a built-in way to achieve this.

```php
<?php

/** @var Pimple\Container $container */
$container['phpmig.sets'] = function ($container) {
    return array(
        'cms' => array(
            'adapter' => new Adapter\File\Flat('modules/migrationLogs/cms_migrations.log'),
            'migrations_path' => 'migrations/cms'
        ),
        'blog' => array(
            'adapter' => new Adapter\File\Flat('modules/migrationLogs/blog_migrations.log'),
            'migrations_path' => 'migrations/blog'
        )
    );
};
```

this way each set has their own migration log and the ability to migrate changes independently of each other.

to run the set migration you just use the command below:

```bash
$ phpmig up -s <SET NAME HERE> --<VERSION HERE>
```

For example, if a change was made to the cms migration, you'll type in this command:

```bash
$ phpmig up -s cms --2
```

and the migration tool will run the migration setup for cms.

to downgrade a migration would be:

```bash
$ phpmig down -s <SET NAME HERE> --<VERSION HERE>
```

Multi path migrations
---------------------

By default you have to provide the path to migrations directory, but you can
organize your migrations script however you like and have several migrations
directory.  To do this you can provide an array of migration file paths to the
container :

```php
<?php

/** @var Pimple\Container $container */
$container['phpmig.migrations'] = function () {
    return array_merge(
        glob('migrations_1/*.php'),
        glob('migrations_2/*.php')
    );
};
```

You can then provide a target directory to the generate command. The target
directory is mandatory if you haven't provided a `phpmig.migrations_path` config
value.

```bash
$ phpmig generate AddRatingToLolCats ./migrations
```

Rolling Back
------------

You can roll back the last run migration by using the rollback command

```bash
$ phpmig rollback
```

To rollback all migration up to a specific migration you can specify the
rollback target

```bash
$ phpmig rollback -t 20111101000144
```

or

```bash
$ phpmig rollback --target=20111101000144
```

By specifying 0 as the rollback target phpmig will revert all migrations 

```bash
$ phpmig rollback -t 0
```

You can also rollback only a specific migration using the down command

```bash
$ phpmig down 20111101000144
```

Using Outside CLI
-----------------
In order to use the migration tool outside the cli context use `Phpmig\Api\PhpmigApplication`.

```php
<?php

use Phpmig\Api\PhpmigApplication;

// require the composer autoloader
require __DIR__ . '/vendor/autoload.php';

$output = new \Symfony\Component\Console\Output\NullOutput();

// create container from bootstrap file
$container = require __DIR__ . '/tests/dom/phpmig.php';

$app = new PhpmigApplication($container, $output);

// run the migrations
$app->up();
```

Todo
----

* Some sort of migration manager, that will take some of the logic out of the
  commands for calculating which migrations have been run, which need running
  etc
* Adapters for Zend\_Db and/or Zend\_Db\_Table and others?
* Redo and rollback commands
* Tests!
* Configuration? 
* Someway of protecting against class definition clashes with regard to the
  symfony dependencies and the user supplied bootstrap?

Contributing
------------

Feel free to fork and send me pull requests, I try and keep the tool really
basic, if you want to start adding tons of features to phpmig, I'd recommend
taking a look at [robmorgan/phinx](https://github.com/robmorgan/phinx).

Inspiration
-----------

I basically started copying [ActiveRecord::Migrations][activerecordmigrations]
in terms of the migration features, the bootstrapping was my own idea, the
layout of the code was inspired by [Symfony][symfony] and [Behat][behat]

Copyright
---------

[Pimple][pimple] is copyright Fabien Potencier. Everything I haven't copied from
anyone else is Copyright (c) 2011 Dave Marshall. See LICENCE for further details


[pimple]:https://github.com/fabpot/Pimple
[doctrinemigrations]:https://github.com/doctrine/migrations
[doctrine]:https://github.com/doctrine
[behat]:http://behat.org/
[symfony]:http://symfony.com/
[activerecordmigrations]:http://api.rubyonrails.org/classes/ActiveRecord/Migration.html
[doctrineschemamanager]:http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/schema-manager.html
