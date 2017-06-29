<?php

if (!file_exists($autoload_file = __DIR__.'/../vendor/autoload.php')) {
    if (empty($_SERVER['OAUTH2BUNDLE_VENDOR_AUTOLOAD'])) {
        throw new Exception('You must run composer.phar install, or set the OAUTH2BUNDLE_VENDOR_AUTOLOAD environment variable in your phpunit.xml to run the bundle tests');
    }

    $autoload_file = $_SERVER['OAUTH2BUNDLE_VENDOR_AUTOLOAD'];
}

require_once $autoload_file;