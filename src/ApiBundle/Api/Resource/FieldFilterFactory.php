<?php

namespace ApiBundle\Api\Resource;

use ApiBundle\Api\Annotation\ApiConf;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;

class FieldFilterFactory
{
    /**
     * @var CachedReader
     */
    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function createFilter($resource, $method)
    {
        $annotation = $this->annotationReader->getMethodAnnotation(
            new \ReflectionMethod(get_class($resource), $method),
            ApiConf::class
        );

        if ($annotation->getFilter() && class_exists($annotation->getFilter())) {
            return new $annotation->getFilter();
        }

       $filterClass = get_class($resource).'Filter';
        if (class_exists($filterClass)) {
            return new $filterClass();
        }

        return null;
    }
}