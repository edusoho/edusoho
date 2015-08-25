<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var $loader ClassLoader
 */
$loader = require __DIR__.'/../vendor2/autoload.php';

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
