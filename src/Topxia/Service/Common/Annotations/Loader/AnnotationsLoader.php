<?php

namespace Topxia\Service\Common\Annotations\Loader;

use Topxia\Service\Common\Annotations\Reader\SimpleAnnotationReader;

class AnnotationsLoader
{
    public static function load($class)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class));
        }

        $reader = new SimpleAnnotationReader();

        $collection = array();
        foreach ($class->getMethods() as $method) {
            foreach ($reader->getMethodAnnotations($method) as $annot) {
                var_dump($annot);exit();
                $collection[] = $annot;
            }
        }

        return $collection;
    }
}
