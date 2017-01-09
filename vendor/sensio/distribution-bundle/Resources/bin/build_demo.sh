#!/bin/bash

# This file is part of the Symfony Standard Edition.
#
# (c) Fabien Potencier <fabien@symfony.com>
#
# For the full copyright and license information, please view the LICENSE
# file that was distributed with this source code.

if [ ! $1 ]; then
    echo "\033[37;41mYou must pass the build dir as an absolute path\033[0m"
    exit 1
fi

DIR=$1
CURRENT=`php -r "echo realpath(dirname(\\$_SERVER['argv'][0]));"`

if [[ ! "$DIR" = /* ]]; then
     DIR="$CURRENT/$DIR"
fi

if [ ! -d $DIR ]; then
    echo "\033[37;41mThe build dir does not exist\033[0m"
    exit 1
fi

# avoid the creation of ._* files
export COPY_EXTENDED_ATTRIBUTES_DISABLE=true
export COPYFILE_DISABLE=true

# Prepare temp. dir
rm -rf /tmp/Symfony
mkdir /tmp/Symfony

# Clone demo application and install its dependencies
git clone https://github.com/symfony/symfony-demo /tmp/Symfony
cd /tmp/Symfony
composer install --prefer-dist --no-interaction --ignore-platform-reqs --no-plugins --optimize-autoloader

# cleanup
cd /tmp/Symfony
rm -f UPGRADE*
mv .gitignore keep.gitignore
rm -rf app/cache/* app/logs/* .git*
mv keep.gitignore .gitignore
chmod 777 app/cache app/logs
find . -name .DS_Store | xargs rm -rf -

# remove unneded dependencies files
cd /tmp/Symfony
TARGET=/tmp/Symfony/vendor

# Doctrine
cd $TARGET/doctrine/orm && rm -rf UPGRADE* build* bin tests tools lib/vendor
cd $TARGET/doctrine/dbal && rm -rf bin build* tests lib/vendor
cd $TARGET/doctrine/common && rm -rf build* tests lib/vendor
if [ -d $TARGET/doctrine/doctrine-bundle/Doctrine/Bundle/DoctrineBundle ]; then
    cd $TARGET/doctrine/doctrine-bundle/Doctrine/Bundle/DoctrineBundle && rm -rf Tests Resources/doc
else
    cd $TARGET/doctrine/doctrine-bundle && rm -rf Tests Resources/doc
fi

# kriswallsmith
cd $TARGET/kriswallsmith/assetic && rm -rf CHANGELOG* phpunit.xml* tests docs

# Monolog
cd $TARGET/monolog/monolog && rm -rf README.markdown phpunit.xml* tests

# Sensio
cd $TARGET/sensio/distribution-bundle/Sensio/Bundle/DistributionBundle && rm -rf phpunit.xml* Tests CHANGELOG* Resources/doc
cd $TARGET/sensio/framework-extra-bundle/Sensio/Bundle/FrameworkExtraBundle && rm -rf phpunit.xml* Tests CHANGELOG* Resources/doc
cd $TARGET/sensio/generator-bundle/Sensio/Bundle/GeneratorBundle && rm -rf phpunit.xml* Tests CHANGELOG* Resources/doc

# Swiftmailer
cd $TARGET/swiftmailer/swiftmailer && rm -rf CHANGES README* build* docs notes test-suite tests create_pear_package.php package*

# Symfony
cd $TARGET/symfony/symfony && rm -rf README.md phpunit.xml* tests *.sh vendor

if [ -d $TARGET/symfony/assetic-bundle/Symfony/Bundle/AsseticBundle ]; then
    cd $TARGET/symfony/assetic-bundle/Symfony/Bundle/AsseticBundle && rm -rf Tests Resources/doc
else
    cd $TARGET/symfony/assetic-bundle && rm -rf Tests Resources/doc
fi

if [ -d $TARGET/symfony/swiftmailer-bundle/Symfony/Bundle/SwiftmailerBundle ]; then
    cd $TARGET/symfony/swiftmailer-bundle/Symfony/Bundle/SwiftmailerBundle && rm -rf Tests Resources/doc
else
    cd $TARGET/symfony/swiftmailer-bundle && rm -rf Tests Resources/doc
fi

if [ -d $TARGET/symfony/monolog-bundle/Symfony/Bundle/MonologBundle ]; then
    cd $TARGET/symfony/monolog-bundle/Symfony/Bundle/MonologBundle && rm -rf Tests Resources/doc
else
    cd $TARGET/symfony/monolog-bundle && rm -rf Tests Resources/doc
fi

# Twig
cd $TARGET/twig/twig && rm -rf AUTHORS CHANGELOG README.markdown bin doc package.xml.tpl phpunit.xml* test
cd $TARGET/twig/extensions && rm -rf README doc phpunit.xml* test

# final cleanup
find $TARGET -name .git | xargs rm -rf -
find $TARGET -name .gitignore | xargs rm -rf -
find $TARGET -name .gitmodules | xargs rm -rf -
find $TARGET -name .svn | xargs rm -rf -

# build ZIP and TGZ packages
cd /tmp
tar zcpf $DIR/Symfony_Demo.tgz Symfony
rm -f $DIR/Symfony_Demo.zip
zip -rq $DIR/Symfony_Demo.zip Symfony
