<?php

$loader = require __DIR__.'/vendor/autoload.php';
\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return require __DIR__.'/bootstrap/bootstrap_phpmig.php';
