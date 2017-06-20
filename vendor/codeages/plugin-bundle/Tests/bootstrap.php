<?php

if (!($loader = @include __DIR__.'/../vendor/autoload.php')) {
    echo <<<'EOT'
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev
$ phpunit
EOT;
    exit(1);
}
