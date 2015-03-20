<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var $loader ClassLoader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

$loader->add('Zend', __DIR__.'/../vendor/zf2/library');
$loader->add('System_', __DIR__.'/../vendor/pear');
$loader->add('Sphinx', __DIR__.'/../vendor/sphinx-client');

return $loader;
