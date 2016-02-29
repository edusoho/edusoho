<?php

namespace Topxia\Service\Common\Annotations\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
use Topxia\Service\Common\Annotations\Reader\FileCacheReader;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Common\Annotations\Annotation;

class AnnotationsLoader
{
    protected static $reader;

    public static function load($class)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class));
        }


        if (!self::$reader) {
            $env = ServiceKernel::instance()->getEnvironment();
            $cacheDir = ServiceKernel::instance()->getParameter('kernel.root_dir').'/cache/'.$env.'/annotations/topxia';
            $debug = $env !== 'prod';
            self::$reader = new FileCacheReader(new AnnotationReader(), $cacheDir, $debug);
        }
        

        $collection = array();
        foreach ($class->getMethods() as $method) {
            foreach (self::$reader->getMethodAnnotations($method) as $annot) {
                if (!$annot instanceof Annotation) {
                    throw new \InvalidArgumentException(sprintf('"%s" must be instance of Topxia\Service\Common\Annotations\Annotation.', get_class($annot)));
                }
                $collection[$method->getName()] = $annot;
            }
        }

        return $collection;
    }
}
